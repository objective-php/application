<?php

namespace ObjectivePHP\Application;

/**
 * Interface Application
 *
 * Objective PHP applications are designed on :
 *     - the Observer design pattern
 *     - Middleware oriented application
 *     - Hexagonal applications
 *
 * @package ObjectivePHP\Application
 */
interface ApplicationInterface
{
    /**
     * Objective PHP application EventsHandler.
     *
     * @return EventsHandlerInterface
     */
    public function getEventsHandler(): EventsHandlerInterface;

    /**
     * @deprecated What should we do about it ?
     */
    public function plug(Pluggable $component): ApplicationInterface;
}