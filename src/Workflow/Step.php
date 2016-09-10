<?php

    namespace ObjectivePHP\Application\Workflow;
    
    
    use ObjectivePHP\Application\Exception;
    use ObjectivePHP\Application\Middleware\EmbeddedMiddleware;
    use ObjectivePHP\Application\Middleware\MiddlewareInterface;
    use ObjectivePHP\Application\Workflow\Filter\FiltersHandler;
    use ObjectivePHP\Primitives\Collection\Collection;

    /**
     * Class Step
     *
     * @package ObjectivePHP\Application
     */
    class Step extends Collection
    {

        use FiltersHandler;
        
        protected $name;

        protected $lastOperation;

        /**
         * @param string $name
         * @param array  $input
         */
        public function __construct($name, array $input = [])
        {
            parent::__construct($input);

            $this->setName($name);

            $this->restrictTo(Hook::class, false);
        }


        /**
         * @param      $operation
         * @param      ...$filters
         */
        public function plug($middleware, ...$filters)
        {

            if(!$middleware instanceof MiddlewareInterface)
            {
                $middleware = new EmbeddedMiddleware($middleware);
            }

            $this->append((new Hook($middleware, ...$filters))->setStep($this));

            // store index for further use
            $this->lastOperation = (string) $this->keys()->last();

            return $this;
        }
    
        /**
         * @param      $operation
         * @param      ...$filters
         *
         * @return $this
         */
        public function plugFirst($middleware, ...$filters)
        {

            if (!$middleware instanceof MiddlewareInterface)
            {
                $middleware = new EmbeddedMiddleware($middleware);
            }

            $this->prepend((new Hook($middleware, ...$filters))->setStep($this));

            // store index for further use
            $this->lastOperation = (string) $this->keys()->last();

            return $this;
        }
        /**
         * @return string
         */
        public function getName()
        {
            return $this->name;
        }

        /**
         * @param string $name
         *
         * @return $this
         */
        public function setName($name)
        {
            $this->name = $name;

            return $this;
        }
    
        /**
         * @param $reference
         *
         * @return $this
         */
        public function as($reference)
        {
            if($this->lastOperation === null)
            {
                throw new Exception('No middleware is expecting an alias. This method should be called immediately after plug()');
            }
            $this->rename($this->lastOperation, $reference);

            $this->lastOperation = null;
            return $this;
        }

        /**
         * @param $reference
         */
        public function asDefault($reference)
        {
            if($this->lastOperation === null)
            {
                throw new Exception('No middleware is expecting an alias. This method should be called immediately after plug()');
            }

            if(!$this->has($reference))
            {
                $this->rename($this->lastOperation, $reference);

                // inject alias as Middleware label
                $middleware = $this[$reference]->getMiddleware();
                $middleware->setReference($reference);
                // also set label if none is defined
                if(!$middleware->getLabel()) $middleware->setLabel($reference);

                $this->lastOperation = null;
            }

            return $this;
        }

    }
