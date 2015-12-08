<?php
    /**
     * Created by PhpStorm.
     * User: gauthier
     * Date: 07/12/2015
     * Time: 13:42
     */
    
    namespace ObjectivePHP\Application\Middleware;

    use ObjectivePHP\Application\ApplicationInterface;

    /**
     * Class AbstractMiddleware
     *
     * @package ObjectivePHP\Application\Hook
     */
    abstract class AbstractMiddleware implements MiddlewareInterface
    {

        protected $label;

        protected $reference;

        /**
         * @return mixed
         */
        public function getLabel()
        {
            return $this->label;
        }

        /**
         * @param mixed $label
         *
         * @return $this
         */
        public function setLabel($label)
        {
            $this->label = $label;

            return $this;
        }

        /**
         * @return mixed
         */
        public function getReference()
        {
            return $this->reference;
        }

        /**
         * @param mixed $reference
         *
         * @return $this
         */
        public function setReference($reference)
        {
            $this->reference = $reference;

            return $this;
        }

        /**
         * @return string
         */
        public function getDetails()
        {
            return 'Middleware ' . get_class($this);
        }

        /**
         * Forward calls on this object to run()
         *
         * @param ...$args
         *
         * @return mixed
         */
        public function __invoke(ApplicationInterface $app)
        {
            return $this->run($app);
        }
    }