<?php

    namespace ObjectivePHP\Application\Workflow;
    
    
    use ObjectivePHP\Application\Exception;
    use ObjectivePHP\Application\Middleware\EmbeddedMiddleware;
    use ObjectivePHP\Application\Middleware\MiddlewareInterface;
    use ObjectivePHP\Primitives\Collection\Collection;

    /**
     * Class Hook
     *
     * @package ObjectivePHP\Application
     */
    class Step extends Collection
    {

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
         * @param      $routeFilter
         * @param null $asserter
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
         * @param      $routeFilter
         * @param null $asserter
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
         * @return mixed
         */
        public function getName()
        {
            return $this->name;
        }

        /**
         * @param mixed $name
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

                $this->lastOperation = null;
            }

            return $this;
        }



    }