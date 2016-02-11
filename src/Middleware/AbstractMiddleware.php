<?php
    /**
     * Created by PhpStorm.
     * User: gauthier
     * Date: 07/12/2015
     * Time: 13:42
     */
    
    namespace ObjectivePHP\Application\Middleware;

    use ObjectivePHP\Application\ApplicationInterface;
    use ObjectivePHP\Notification\Stack;
    use ObjectivePHP\ServicesFactory\ServicesFactory;

    /**
     * Class AbstractMiddleware
     *
     * @package ObjectivePHP\Application\Hook
     */
    abstract class AbstractMiddleware implements MiddlewareInterface
    {

        /**
         * @var string
         */
        protected $label;

        /**
         * @var string
         */
        protected $reference;

        /**
         * @var Stack
         */
        protected $notifications;

        /**
         * @return mixed
         */
        public function getLabel()
        {
            return $this->label ?? 'anonymous';
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
            return $this->reference ?? 'n/a';
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
        public function getDescription() : string
        {
            return 'Middleware ' . get_class($this);
        }

        public function getNotifications() : Stack
        {
            if(is_null($this->notifications))
            {
                $this->notifications = new Stack();
            }

            return $this->notifications;
        }

        /**
         * Run the operation
         *
         * @param mixed ...$args
         *
         * @return mixed
         */
        public function __invoke(...$args)
        {
            // TODO: Implement __invoke() method.
        }

        /**
         * @param ServicesFactory $factory
         *
         * @return mixed
         */
        public function setServicesFactory(ServicesFactory $factory)
        {
            // TODO: Implement setServicesFactory() method.
        }


    }
