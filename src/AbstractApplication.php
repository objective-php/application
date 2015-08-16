<?php

    namespace ObjectivePHP\Application;
    
    
    use ObjectivePHP\Events\EventsHandler;
    use ObjectivePHP\ServicesFactory\ServicesFactory;
    use ObjectivePHP\Workflow\WorkflowInterface;

    abstract class AbstractApplication implements ApplicationInterface
    {
        /**
         * @var EventsHandler
         */
        protected $eventsHandler;

        /**
         * @var ServicesFactory
         */
        protected $servicesFactory;

        /**
         * @var WorkflowInterface
         */
        protected $workflow;

        /**
         * @var string
         */
        protected $env;


        public function run()
        {
            $this->getWorkflow()->setEventsHandler($this->getEventsHandler());


            $this->getWorkflow()->run();
        }

        /**
         * @return EventsHandler
         */
        public function getEventsHandler()
        {

            if(is_null($this->eventsHandler))
            {
                $this->eventsHandler = new EventsHandler();
            }

            return $this->eventsHandler;
        }

        /**
         * @param EventsHandler $eventsHandler
         *
         * @return $this
         */
        public function setEventsHandler(EventsHandler $eventsHandler)
        {
            $this->eventsHandler = $eventsHandler;

            return $this;
        }

        /**
         * @return ServicesFactory
         */
        public function getServicesFactory()
        {
            if(is_null($this->servicesFactory))
            {
                $this->servicesFactory = new ServicesFactory();
            }
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
         * @return WorkflowInterface
         */
        public function getWorkflow()
        {
            return $this->workflow;
        }

        /**
         * @param WorkflowInterface $workflow
         *
         * @return $this
         */
        public function setWorkflow(WorkflowInterface $workflow)
        {
            $this->workflow = $workflow;

            return $this;
        }

        /**
         * @return string
         */
        public function getEnv()
        {
            return $this->env;
        }

        /**
         * @param string $env
         *
         * @return $this
         */
        public function setEnv($env)
        {
            $this->env = $env;

            return $this;
        }



    }