<?php

    namespace ObjectivePHP\Application\Workflow;

    use ObjectivePHP\Application\ApplicationInterface;
    use ObjectivePHP\Application\Exception;
    use ObjectivePHP\Application\Middleware\MiddlewareInterface;
    use ObjectivePHP\Application\Workflow\Filter\EncapsulatedFilter;
    use ObjectivePHP\Application\Workflow\Filter\FilterInterface;
    use ObjectivePHP\Application\Workflow\Filter\UrlFilter;
    use ObjectivePHP\Primitives\Collection\Collection;
    use ObjectivePHP\ServicesFactory\ServiceReference;


    /**
     * Class Hook
     *
     * @package ObjectivePHP\Application
     */
    class Hook
    {
        /**
         * @var
         */
        protected $middleware;

        /**
         * @var Collection
         */
        protected $filters = [];

        /**
         * @var Step
         */
        protected $step;

        /**
         * Hook constructor.
         *
         * @param MiddlewareInterface $middleware
         * @param string              $url
         * @param null                $asserter
         */
        public function __construct(MiddlewareInterface $middleware, ...$filters)
        {
            $this->setMiddleware($middleware);
            $this->setFilters(Collection::cast($filters));
        }

        /**
         * @param ApplicationInterface $app
         *
         * @return null
         * @throws Exception
         */
        public function run(ApplicationInterface $app)
        {

            // filter call
            if (!$this->filter($app)) return null;

            $app->getEventsHandler()->trigger('application.workflow.hook.run', $this);
            $middleware = $this->getMiddleware();

            return $middleware($app);
        }

        /**
         * @param ApplicationInterface $app
         *
         * @return bool
         * @throws Exception
         */
        protected function filter(ApplicationInterface $app)
        {

            foreach ($this->getFilters() as $filter)
            {
                // normalize filters
                if (!$filter instanceof FilterInterface)
                {

                    if (is_string($filter) && class_exists($filter))
                    {
                        $filter = new $filter;
                    }
                    elseif ($filter instanceof ServiceReference)
                    {
                        $filter = $app->getServicesFactory()->get($filter);
                    }

                    if (!$filter instanceof FilterInterface)
                    {
                        if (is_callable($filter))
                        {
                            $filter = new EncapsulatedFilter($filter);
                        }
                        elseif (is_string($filter))
                        {
                            // default to UrlFilter
                            $filter = new UrlFilter($filter);
                        }
                        else
                        {
                            throw new Exception('Invalid filter');
                        }
                    }
                }

                if (!$filter->filter($app)) return false;
            };

            return true;
        }

        /**
         * @return Collection
         */
        public function getFilters()
        {
            return $this->filters;
        }

        /**
         * @param Collection $filters
         *
         * @return $this
         */
        public function setFilters($filters)
        {
            $this->filters = $filters;

            return $this;
        }

        /**
         * @return MiddlewareInterface
         */
        public function getMiddleware()
        {
            return $this->middleware;
        }

        /**
         * @param mixed $middleware
         *
         * @return $this
         */
        public function setMiddleware(MiddlewareInterface $middleware)
        {
            $this->middleware = $middleware;

            return $this;
        }


        /**
         * @return Step
         */
        public function getStep()
        {
            return $this->step;
        }

        /**
         * @param Step $step
         *
         * @return $this
         */
        public function setStep($step)
        {
            $this->step = $step;

            return $this;
        }

    }
