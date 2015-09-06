<?php

    namespace ObjectivePHP\Application\Action;

    use ObjectivePHP\Application\ApplicationInterface;
    use ObjectivePHP\Application\Action\Param\ParameterInterface;
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

            // get parameters expectations
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
                $expectation = $expectedParams->get($name);
                $processedValue = $expectation->setApplication($this->getApplication())->process($value);

                // check that processed value is not empty if expected param is mandatory
                if($expectation->isMandatory() && !$processedValue)
                {
                    throw new Exception($expectation->getMessage() . ' (Note that value was empty after processing -  was "' . $value . '" before)');
                }


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
         * @return ParameterInterface[]
         */
        public function expects()
        {
            return [];
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
         * @param $params
         *
         * @throws \ObjectivePHP\Primitives\Exception
         */
        public function setParams($params)
        {
            $params = Collection::cast($params)->copy();

            // fulfill expectations
            $this->getExpectations()->each(function(ParameterInterface $expectation) use($params)
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
        public function setExpectation(ParameterInterface ...$expectations)
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