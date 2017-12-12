<?php

namespace ObjectivePHP\Application\Middleware;

/**
 * Interface MiddlewareFiltersProviderInterface
 *
 * @package ObjectivePHP\Application\Middleware
 */
interface WorkflowFiltersProviderInterface extends MiddlewareInterface
{
    /**
     * Run the embedded filters
     *
     * @return bool
     */
    public function runFilters(): bool;
}
