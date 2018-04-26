<?php

namespace ObjectivePHP\Application\Exception\Filter;

use ObjectivePHP\Application\Filter\FilterInterface;

/**
 * Class AbstractFilter
 *
 * @package ObjectivePHP\Application\Workflow
 */
abstract class AbstractWorkflowFilter implements FilterInterface
{
    protected $filter;

    /**
     * RouteFilter constructor.
     *
     * @param $filter
     */
    public function __construct($filter)
    {
        $this->filter = $filter;
    }

    /**
     * @return mixed
     */
    public function getFilter()
    {
        return $this->filter;
    }
}
