<?php
    
    namespace ObjectivePHP\Application;

    use ObjectivePHP\Config\Config;
    use ObjectivePHP\Events\EventsHandler;
    use ObjectivePHP\Invokable\InvokableInterface;
    use ObjectivePHP\ServicesFactory\ServicesFactory;
    use Psr\Http\Message\ResponseInterface;
    use Psr\Http\Message\ServerRequestInterface;
    use Psr\Http\Server\RequestHandlerInterface;

    /**
     * Interface ApplicationInterface
     *
     * @package ObjectivePHP\Application
     */
    interface ApplicationInterface extends RequestHandlerInterface
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
         * @param ServerRequestInterface $request
         *
         * @return ApplicationInterface
         */
        public function setRequest(ServerRequestInterface $request) : ApplicationInterface;

        /**
         * @return ServerRequestInterface
         */
        public function getRequest() : ServerRequestInterface;

        /**
         * @param ResponseInterface $response
         *
         * @return ApplicationInterface
         */
        public function setResponse(ResponseInterface $response) : ApplicationInterface;

        /**
         * @return ResponseInterface
         */
        public function getResponse() : ResponseInterface;

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
