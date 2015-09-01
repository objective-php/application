<?php

    namespace ObjectivePHP\Application\Action;

    use ObjectivePHP\Application\ApplicationInterface;
    use ObjectivePHP\Application\Action\Param\ExpectationInterface;
    use ObjectivePHP\Application\Exception;
    use ObjectivePHP\Application\Workflow\Event\WorkflowEvent;
    use ObjectivePHP\Events\EventsHandler;
    use ObjectivePHP\Primitives\Collection\Collection;
    use ObjectivePHP\ServicesFactory\ServicesFactory;

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
        protected $expectations;

        /**
         * @var Collection
         */
        protected $params;

        /**
         * @var string
         */
        protected $viewName;

        public function __invoke(WorkflowEvent $event)
        {
            $this->setServicesFactory($event->getApplication()->getServicesFactory());
            $this->setApplication($event->getApplication());
            $this->setEventsHandler($event->getApplication()->getEventsHandler());

            // get expectations
            $expectations = $this->expects();

            $expectations = Collection::cast($expectations)->toArray();
            $this->setExpectation(...$expectations);


            // set params
            $this->params = new Collection();
            $this->setParams($this->getApplication()->getRequest()->getParameters()->fromGet());

            // actually execute action
            return $this->run($event);

        }

        abstract public function run(WorkflowEvent $event);

        public function getServicesFactory()
        {
            return $this->servicesFactory;
        }

        public function setServicesFactory(ServicesFactory $servicesFactory)
        {
            $this->servicesFactory = $servicesFactory;

            return $this;
        }

        public function setParam($name, $value)
        {
            $expectedParams = $this->getExpectations();

            if ($expectedParams->has($name))
            {
                $processedValue = $expectedParams->get($name)->setApplication($this->getApplication())->process($value);

                // use alias as name if any
                $name = $expectedParams->get($name)->getAlias() ?: $name;
            }
            else
            {
                // keep unexpected params anyway
                $processedValue = $value;
            }

            $this->params[$name] = $processedValue;

            return $this;
        }

        public function getParam($param, $default = null)
        {
            return $this->params->get($param, $default);
        }

        public function getParams()
        {
            return $this->params;
        }

        /**
         * Tells action what parameter are expected (if any)
         *
         * This should be implemented in inherited classes, but
         * is not mandatory
         *
         * @return ExpectationInterface[]
         */
        public function expects()
        {
            return [];
        }

        /**
         * @param $params
         *
         * @throws \ObjectivePHP\Primitives\Exception
         */
        public function setParams($params)
        {
            $params = Collection::cast($params)->copy();


            // fulfill expectations
            $this->getExpectations()->each(function(ExpectationInterface $expectation) use($params)
            {

                $expectationValue = $params->get($expectation->getReference());

                if($expectation->isMandatory() && is_null($expectationValue))
                {
                   throw new Exception($expectation->getMessage());
                }
                else
                {
                    $this->setParam($expectation->getReference(), $expectationValue);
                }

            });

            // set unexpected params
            Collection::cast($params)->each(function($value, $param)
            {
                $this->setParam($param, $value);
            });

            return $this;
        }

        /**
         * @return Collection
         */
        public function getExpectations()
        {
            return $this->expectations;
        }

        /**
         * @param Collection $expectations
         *
         * @return $this
         */
        public function setExpectation(ExpectationInterface ...$expectations)
        {
            $this->expectations = Collection::cast($expectations);

            return $this;
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