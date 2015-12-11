<?php

    namespace ObjectivePHP\Application\Workflow\Filter;

    use ObjectivePHP\Invokable\InvokableInterface;

    /**
     * Class AbstractFilter
     *
     * @package ObjectivePHP\Application\Workflow
     */
    abstract class AbstractFilter implements InvokableInterface
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