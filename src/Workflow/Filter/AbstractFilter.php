<?php

    namespace ObjectivePHP\Application\Workflow\Filter;

    /**
     * Class AbstractFilter
     *
     * @package ObjectivePHP\Application\Workflow
     */
    abstract class AbstractFilter implements FilterInterface
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