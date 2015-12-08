<?php

    namespace ObjectivePHP\Application\Middleware;

    use ObjectivePHP\Application\ApplicationInterface;

    /**
     * Interface MiddlewareInterface
     *
     * @package ObjectivePHP\Application\Hook
     */
    interface MiddlewareInterface
    {
        /**
         * @param ApplicationInterface $application
         *
         * @return mixed
         */
        public function __invoke(ApplicationInterface $application);

        public function getLabel();

        public function getReference();

        public function getDetails();
    }