<?php

namespace ObjectivePHP\Application;

/**
 * Interface EventsHandler
 * @package ObjectivePHP\Application
 */
interface EventsHandlerInterface
{
    /**
     * @param string $evenName
     * @param callable $callback
     * @return mixed
     */
    public function bind(string $evenName, callable $callback);

    /**
     * @param string $eventName
     * @param $context
     * @return mixed
     */
    public function trigger(string $eventName, $context);
}
