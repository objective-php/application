<?php

    namespace ObjectivePHP\Application\Workflow;

    use ObjectivePHP\Application\ApplicationInterface;
    use ObjectivePHP\Application\Exception;
    use ObjectivePHP\Application\Middleware\MiddlewareInterface;
    use ObjectivePHP\Invokable\Invokable;
    use ObjectivePHP\Invokable\InvokableInterface;
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
            $this->setFilters($filters);
        }

        /**
         * @param ApplicationInterface $app
         *
         * @return null
         * @throws Exception
         */
        public function run(ApplicationInterface $app)
        {
            try
            {
                // filter call
                if (!$this->filter($app)) return null;

                $app->getEventsHandler()->trigger('application.workflow.hook.run', $this);

                $middleware = $this->getMiddleware();
                if($middleware instanceof InvokableInterface) $middleware->setServicesFactory($app->getServicesFactory());
                return $middleware($app);

            }
            catch(\Throwable $e)
            {
                throw new Exception('Failed running hook "' . $middleware->getReference() . '" of type: ' . $middleware->getDescription(), null, $e);
            }
        }

        /**
         * @param ApplicationInterface $app
         *
         * @return bool
         * @throws Exception
         */
        protected function filter(ApplicationInterface $app)
        {

            /**
             * @var callable $filter
             */
            foreach($this->getFilters() as $filter)
            {
                if (!$filter($app))
                {
                    return false;
                }
            }

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

            $this->filters = (new Collection())->restrictTo(InvokableInterface::class);

            Collection::cast($filters)->each(function($filter) {
                $this->filters->append(Invokable::cast($filter));
            });

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
