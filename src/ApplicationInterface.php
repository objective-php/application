<?php
    
    namespace ObjectivePHP\Application;

    use ObjectivePHP\Application\Workflow\Step;
    use ObjectivePHP\Application\Workflow\WorkflowInterface;
    use ObjectivePHP\Config\Config;
    use ObjectivePHP\Events\EventsHandler;
    use ObjectivePHP\Message\Request\RequestInterface;
    use ObjectivePHP\Message\Response\ResponseInterface;
    use ObjectivePHP\Primitives\Collection\Collection;
    use ObjectivePHP\ServicesFactory\ServicesFactory;

    /**
     * Interface ApplicationInterface
     *
     * @package ObjectivePHP\Application
     */
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
         * @param RequestInterface $request
         *
         * @return mixed
         */
        public function setRequest(RequestInterface $request);

        /**
         * @return RequestInterface
         */
        public function getRequest();

        /**
         * @param ResponseInterface $request
         *
         * @return $this
         */
        public function setResponse(ResponseInterface $request);

        /**
         * @return ResponseInterface
         */
        public function getResponse();

        /**
         * @param $step
         *
         * @return Step
         */
        public function on($step) : Step;

        /**
         * @return Collection
         */
        public function getSteps() : Collection;

    }