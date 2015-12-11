<?php
    
    namespace ObjectivePHP\Application\Middleware;
    
    
    use ObjectivePHP\Application\ApplicationInterface;
    use ObjectivePHP\Invokable\Invokable;
    use ObjectivePHP\Invokable\InvokableInterface;
    use ObjectivePHP\ServicesFactory\ServiceReference;

    /**
     * Class EmbeddedMiddleware
     *
     * @package ObjectivePHP\Application\Hook
     */
    class EmbeddedMiddleware extends AbstractMiddleware
    {

        /**
         * @var
         */
        protected $operation;

        /**
         * EmbeddedMiddleware constructor.
         *
         * @param $operation
         */
        public function __construct($operation)
        {
            $this->operation = Invokable::cast($operation);
        }

        /**
         * @param ApplicationInterface $app
         *
         * @return mixed
         */
        public function run(ApplicationInterface $app)
        {
            $operation =  $this->getOperation();

            return $operation($app);
        }

        /**
         * @return InvokableInterface
         */
        public function getOperation()
        {
            return $this->operation;
        }

        /**
         * @return string
         */
        public function getDescription() : string
        {
            return 'Middleware embedding ' . $this->getOperation()->getDescription();
        }
    }