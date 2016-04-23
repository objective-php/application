<?php
    
    namespace ObjectivePHP\Application;

    use ObjectivePHP\Application\Workflow\Step;
    use ObjectivePHP\Application\Workflow\WorkflowInterface;
    use ObjectivePHP\Config\Config;
    use ObjectivePHP\Events\EventsHandler;
    use ObjectivePHP\Invokable\InvokableInterface;
    use ObjectivePHP\Message\Request\RequestInterface;
    use ObjectivePHP\Message\Response\ResponseInterface;
    use ObjectivePHP\Primitives\Collection\Collection;
    use ObjectivePHP\ServicesFactory\ServicesFactory;
    use Zend\Diactoros\Response;

    /**
     * Interface ApplicationInterface
     *
     * @package ObjectivePHP\Application
     */
    interface ApplicationInterface
    {

        /**
         * @return mixed
         */
        public function init();

        /**
         * @return mixed
         */
        public function run();

        /**
         * @return EventsHandler
         */
        public function getEventsHandler() : EventsHandler;

        /**
         * @return ServicesFactory
         */
        public function getServicesFactory() : ServicesFactory;

        /**
         * @return Config
         */
        public function getConfig() : Config;

        /**
         * @param RequestInterface $request
         *
         * @return ApplicationInterface
         */
        public function setRequest(RequestInterface $request) : ApplicationInterface;

        /**
         * @return RequestInterface
         */
        public function getRequest() : RequestInterface;

        /**
         * @param Response $request
         *
         * @return ApplicationInterface
         */
        public function setResponse(Response $request) : ApplicationInterface;

        /**
         * @return ResponseInterface
         */
        public function getResponse() : Response;

        /**
         * @param $step
         *
         * @return Step
         */
        public function getStep($step) : Step;

        /**
         * @return Collection
         */
        public function getSteps() : Collection;

        /**
         * @param \Throwable $exception
         *
         * @return ApplicationInterface
         */
        public function setException(\Throwable $exception) : ApplicationInterface;

        /**
         * @return \Throwable
         */
        public function getException() : \Throwable;

        /**
         * @param mixed $invokable
         *
         * @return ApplicationInterface
         */
        public function setExceptionHandler($invokable) : ApplicationInterface;

        /**
         * @return InvokableInterface
         */
        public function getExceptionHandler() : InvokableInterface;

        /**
         * @return array
         */
        public function getExecutionTrace() : array;


        /**
         * @return string
         */
        public function getEnv() : string;
    }