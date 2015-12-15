<?php

    namespace ObjectivePHP\Application\Middleware;

    use ObjectivePHP\Application\ApplicationInterface;
    use ObjectivePHP\Invokable\InvokableInterface;
    use ObjectivePHP\Notification\Stack;

    /**
     * Interface MiddlewareInterface
     *
     * @package ObjectivePHP\Application\Hook
     */
    interface MiddlewareInterface extends InvokableInterface
    {
        public function getLabel();

        public function getReference();

        public function getNotifications() : Stack;
    }