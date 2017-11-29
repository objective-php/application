<?php

    namespace ObjectivePHP\Application\Workflow;

    use ObjectivePHP\Application\ApplicationInterface;
    use ObjectivePHP\Application\Exception;
    use ObjectivePHP\Application\Middleware\WorkflowFiltersProviderInterface;
    use ObjectivePHP\Application\Middleware\FilteredMiddlewareException;
    use ObjectivePHP\Application\Middleware\MiddlewareInterface;
    use ObjectivePHP\Application\Workflow\Filter\FiltersHandler;
    use ObjectivePHP\Invokable\InvokableInterface;

    /**
     * Class Hook
     *
     * @package ObjectivePHP\Application
     */
    class Hook
    {

        use FiltersHandler;

        /**
         * @var
         */
        protected $middleware;

        /**
         * @var Step
         */
        protected $step;

        /**
         * Hook constructor.
         *
         * @param MiddlewareInterface $middleware
         * @param array               $filters
         *
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
         * @throws \Throwable
         */
        public function run(ApplicationInterface $app)
        {
            try
            {
                // filter call
                if (!$this->runFilters($app)) {
                    return null;
                }
                $app->getEventsHandler()->trigger('application.workflow.hook.run', $this);

                $middleware = $this->getMiddleware();

                if($middleware instanceof InvokableInterface) {
                    $middleware->setApplication($app);
                }

                if ($middleware instanceof WorkflowFiltersProviderInterface && !$middleware->runFilters()) {
                    throw new FilteredMiddlewareException();
                }

                $callable = ($middleware instanceof InvokableInterface) ? $middleware->getCallable() : $middleware;

                if(is_object($callable)) {
                    $app->getServicesFactory()->injectDependencies($callable);
                }

                return $middleware($app);
            }
            catch (FilteredMiddlewareException $e)
            {
                throw $e;
            }
            catch(\Throwable $e)
            {
                if(!empty($middleware))
                {
                    throw new Exception('Failed running hook "' . $middleware->getReference() . '" of type: ' . $middleware->getDescription(), null, $e);
                }
                else {
                    // propagate Exception
                    throw $e;
                }
            }
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
