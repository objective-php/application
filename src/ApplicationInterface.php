<?php
    
    namespace ObjectivePHP\Application;

    use ObjectivePHP\Application\Workflow\WorkflowInterface;
    use ObjectivePHP\Config\Config;
    use ObjectivePHP\Events\EventsHandler;
    use ObjectivePHP\Message\Request\RequestInterface;
    use ObjectivePHP\ServicesFactory\ServicesFactory;

    interface ApplicationInterface
    {

        public function init();

        public function run();

        /**
         * @return EventsHandler
         */
        public function getEventsHandler();

        /**
         * @return ServicesFactory
         */
        public function getServicesFactory();

        /**
         * @return Config
         */
        public function getConfig();

        /**
         * @return WorkflowInterface
         */
        public function getWorkflow();

        /**
         * @param RequestInterface $request
         *
         * @return mixed
         */
        public function setRequest(RequestInterface $request);

        /**
         * @return RequestInterface
         */
        public function getRequest();


    }