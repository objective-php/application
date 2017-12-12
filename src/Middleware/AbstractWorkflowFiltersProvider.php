<?php

namespace ObjectivePHP\Application\Middleware;

use ObjectivePHP\Application\Workflow\Filter\FiltersHandler;

/**
 * Class AbstractFilterableMiddleware
 *
 * @package ObjectivePHP\Application\Middleware
 */
class AbstractWorkflowFiltersProvider extends AbstractMiddleware implements WorkflowFiltersProviderInterface
{
    use FiltersHandler {
        FiltersHandler::runFilters as runProvidedFilters;
    }

    /**
     * {@inheritdoc}
     */
    public function runFilters(): bool
    {
        return $this->runProvidedFilters($this->getApplication());
    }
}
