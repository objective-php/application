<?php

    namespace ObjectivePHP\Application\Middleware;

    use ObjectivePHP\Application\ApplicationInterface;
    use ObjectivePHP\Invokable\AbstractInvokable;
    use ObjectivePHP\Notification\Stack;
    use ObjectivePHP\ServicesFactory\ServicesFactory;

    /**
     * Class AbstractMiddleware
     *
     * @package ObjectivePHP\Application\Hook
     */
    abstract class AbstractMiddleware extends AbstractInvokable implements MiddlewareInterface
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
         * @var ServicesFactory
         */
        protected $servicesFactory;


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
            return $this->reference ?? 'unaliased';
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

        /**
         * @return Stack
         */
        public function getNotifications() : Stack
        {
            if(is_null($this->notifications))
            {
                $this->notifications = new Stack();
            }

            return $this->notifications;
        }


        /**
         * Forward calls on this object to run()
         *
         * @param ApplicationInterface $app
         * @param array                ...$args
         *
         * @return mixed
         */
        public function __invoke(...$args)
        {
            return $this->run(...$args);
        }
    }
