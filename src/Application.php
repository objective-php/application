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
interface Application
{
    /**
     * Objective PHP application EventsHandler.
     *
     * @return EventsHandler
     */
    public function getEventsHandler(): EventsHandler;

    /**
     * Plug any components to your application like Step and Middleware.
     *
     * @param Pluggable $component
     * @return Application
     */
    public function plug(Pluggable $component): Application;

    /**
     * Get a workflow step.
     * Steps are identifiable points on your middleware stack.
     *
     * @param string $step
     * @return Step
     */
    public function getStep(string $step): Step;

    /**
     * Get all workflow steps.
     * Steps are identifiable points on your middleware stack.
     *
     * @return Step[]
     */
    public function getSteps(): array;
}