<?php

namespace ObjectivePHP\Application;

/**
 * interface FilterInterface
 * @package ObjectivePHP\Application
 */
interface FilterProviderInterface
{
    /**
     * @return array
     */
    public function getFilters(): array;

    /**
     * @return FilterProviderInterface
     */
    public function clearFilters(): FilterProviderInterface;
}
