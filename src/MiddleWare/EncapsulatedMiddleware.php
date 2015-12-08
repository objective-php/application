<?php
    /**
     * Created by PhpStorm.
     * User: gauthier
     * Date: 07/12/2015
     * Time: 14:05
     */
    
    namespace ObjectivePHP\Application\Middleware;
    
    
    use ObjectivePHP\Application\ApplicationInterface;
    use ObjectivePHP\ServicesFactory\ServiceReference;

    /**
     * Class EncapsulatedMiddleware
     *
     * @package ObjectivePHP\Application\Hook
     */
    class EncapsulatedMiddleware extends AbstractMiddleware
    {

        /**
         * @var
         */
        protected $operation;

        /**
         * EncapsulatedMiddleware constructor.
         *
         * @param $operation
         */
        public function __construct($operation)
        {
            $this->operation = $operation;
        }

        /**
         * @param ApplicationInterface $app
         *
         * @return mixed
         */
        public function run(ApplicationInterface $app)
        {
            return $app->exec($this->getOperation());
        }

        /**
         * @return mixed
         */
        public function getOperation()
        {
            return $this->operation;
        }

        /**
         * @return string
         */
        public function getDetails()
        {
            $operation = $this->getOperation();
            switch (true)
            {
                case $operation instanceof ServiceReference:
                    return 'Service "' . $operation->getId() . '"';
                    break;

                case $operation instanceof \Closure:
                    $reflected = new \ReflectionFunction($operation);
                    return sprintf('Closure defined in file "%s" on line %d', $reflected->getFileName(), $reflected->getStartLine());
                    break;

                case is_object($operation):
                    return 'Instance of ' . get_class($operation);
                    break;

                case is_string($operation) && class_exists($operation):
                    return 'Invokable class ' . $operation;
                    break;

                default:
                    return 'Unknown operation type';
                    break;
            }
        }
    }