<?php

    namespace ObjectivePHP\Application\Action;

    use ObjectivePHP\Application\Action\Parameter\ParameterProcessorInterface;
    use ObjectivePHP\Application\ApplicationInterface;
    use ObjectivePHP\Application\Workflow\Event\WorkflowEvent;
    use ObjectivePHP\Events\EventsHandler;
    use ObjectivePHP\Primitives\Collection\Collection;
    use ObjectivePHP\ServicesFactory\ServicesFactory;

    /**
     * Class AbstractAction
     *
     * @package ObjectivePHP\Application\Action
     */
    abstract class AbstractAction
    {

        /**
         * @var ServicesFactory
         */
        protected $servicesFactory;

        /**
         * @var ApplicationInterface
         */
        protected $application;

        /**
         * @var EventsHandler
         */
        protected $eventsHandler;

        /**
         * @var Collection
         */
        protected $parameterProcessors;

        /**
         * @var Collection
         */
        protected $params;

        /**
         * @var string
         */
        protected $viewName;

        /**
         *
         */
        public function __construct()
        {
            $this->parameterProcessors = new Collection();

            $this->init();
        }

        /**
         * Delegated constructor
         */
        public function init()
        {

        }

        /**
         * @param WorkflowEvent $event
         *
         * @return mixed
         */
        public function __invoke(WorkflowEvent $event)
        {
            $this->setServicesFactory($event->getApplication()->getServicesFactory());
            $this->setApplication($event->getApplication());
            $this->setEventsHandler($event->getApplication()->getEventsHandler());

            // set params
            $this->params = new Collection();
            $this->setParams($this->getApplication()->getRequest()->getParameters()->fromGet());

            // process parameters
            $this->processParams();

            // actually execute action
            return $this->run($event);

        }

        /**
         * @return ApplicationInterface
         */
        public function getApplication()
        {
            return $this->application;
        }

        /**
         * @param ApplicationInterface $application
         *
         * @return $this
         */
        public function setApplication($application)
        {
            $this->application = $application;

            return $this;
        }

        /**
         * @param $params
         *
         * @throws \ObjectivePHP\Primitives\Exception
         */
        public function processParams()
        {

            // fulfill expectations
            $this->getParameterProcessors()->each(function (ParameterProcessorInterface $parameterProcessor)
            {
                // inject application
                $parameterProcessor->setApplication($this->getApplication());
                $rawValue       = $this->params->get($parameterProcessor->getQueryParameterMapping());
                $processedValue = $parameterProcessor->process($rawValue);
                $this->setParam($parameterProcessor->getReference(), $processedValue);

            })
            ;

            return $this;
        }

        /**
         * @return Collection
         */
        public function getParameterProcessors()
        {
            return $this->parameterProcessors;
        }

        /**
         * @param Collection $processors
         *
         * @return $this
         */
        public function setParameterProcessor(ParameterProcessorInterface ...$processors)
        {
            Collection::cast($processors)->each(function(ParameterProcessorInterface $processor){
                $this->parameterProcessors->set($processor->getReference(), $processor);
            });

            return $this;
        }

        /**
         * @param $name
         * @param $value
         *
         * @return $this
         * @throws \ObjectivePHP\Primitives\Exception
         */
        public function setParam($name, $value)
        {
            $processors = $this->getParameterProcessors();

            if ($processors->has($name))
            {
                $processor      = $processors->get($name);
                $processedValue = $processor->process($value);
                $name           = $processors->get($name)->getReference();
            }
            else
            {
                // keep unexpected params anyway
                $processedValue = $value;
            }

            $this->params->set($name, $processedValue);

            return $this;
        }

        /**
         * @param WorkflowEvent $event
         *
         * @return mixed
         */
        abstract public function run(WorkflowEvent $event);

        /**
         * @param      $param
         * @param null $default
         *
         * @return mixed|null
         * @throws \ObjectivePHP\Primitives\Exception
         */
        public function getParam($param, $default = null)
        {
            return $this->params->get($param, $default);
        }

        /**
         * @return Collection
         */
        public function getParams()
        {
            return $this->params;
        }

        /**
         * @param $params
         *
         * @return $this
         */
        public function setParams($params)
        {
            $this->params = Collection::cast($params);

            return $this;
        }

        /**
         * Return the given service
         *
         * @param $serviceId
         *
         * @return mixed|null
         * @throws \ObjectivePHP\ServicesFactory\Exception
         */
        public function getService($serviceId)
        {
            return $this->getServicesFactory()->get($serviceId);
        }

        /**
         * @return ServicesFactory
         */
        public function getServicesFactory()
        {
            return $this->servicesFactory;
        }

        /**
         * @param ServicesFactory $servicesFactory
         *
         * @return $this
         */
        public function setServicesFactory(ServicesFactory $servicesFactory)
        {
            $this->servicesFactory = $servicesFactory;

            return $this;
        }

        /**
         * @return EventsHandler
         */
        public function getEventsHandler()
        {
            return $this->eventsHandler;
        }

        /**
         * @param EventsHandler $eventsHandler
         *
         * @return $this
         */
        public function setEventsHandler($eventsHandler)
        {
            $this->eventsHandler = $eventsHandler;

            return $this;
        }

        /**
         * @return string
         */
        public function getViewName()
        {
            return $this->viewName;
        }

        /**
         * @param string $viewName
         *
         * @return $this
         */
        public function setViewName($viewName)
        {
            $this->viewName = $viewName;

            return $this;
        }

    }