<?php

    namespace ObjectivePHP\Application\Action;

    use ObjectivePHP\Application\ApplicationInterface;
    use ObjectivePHP\Application\Exception;
    use ObjectivePHP\DataProcessor\DataProcessorInterface;
    use ObjectivePHP\Events\EventsHandler;
    use ObjectivePHP\Invokable\InvokableInterface;
    use ObjectivePHP\Message\Request\Parameter\Container\HttpParameterContainer;
    use ObjectivePHP\Primitives\Collection\Collection;
    use ObjectivePHP\Primitives\String\Str;
    use ObjectivePHP\ServicesFactory\ServicesFactory;
    use ObjectivePHP\ServicesFactory\Specs\InjectionAnnotationProvider;

    /**
     * Class AbstractAction
     *
     * @package ObjectivePHP\Application\Action
     */
    abstract class  HttpAction implements InvokableInterface , InjectionAnnotationProvider
    {

        /**
         * @var ServicesFactory
         */
        protected $servicesFactory;

        /**
         * @var ApplicationInterface
         */
        protected $application;

        /**
         * @var EventsHandler
         */
        protected $eventsHandler;

        /**
         * @var array
         */
        protected $aliases = [];

        /**
         *
         */
        public function __construct()
        {
                $this->params = new Collection();
        }

        /**
         * Delegated constructor
         *
         * This should be overriden in children instead of overriding __construct()
         */
        public function init()
        {

        }

        /**
         * @param array $args
         * @return mixed
         * @throws Exception
         */
        public function __invoke(...$args)
        {

            $app = array_shift($args);

            if(!$app instanceof ApplicationInterface)
            {
                throw new Exception('Action must be invoked with an ApplicationInterface instance as first parameter');
            }


            $this->setApplication($app);
            $this->setServicesFactory($app->getServicesFactory());
            $this->setEventsHandler($app->getEventsHandler());

            // init action
            $this->init();

            // actually execute action
            return $this->run($app);

        }

        /**
         * @return ApplicationInterface
         */
        public function getApplication() : ApplicationInterface
        {
            return $this->application;
        }

        /**
         * @param ApplicationInterface $application
         * @return $this|InvokableInterface
         */
        public function setApplication(ApplicationInterface $application) : InvokableInterface
        {
            $this->application = $application;

            return $this;
        }

        /**
	 * Shorthand to access GET parameters
	 *
         * @param      $param
         * @param null $default
         *
         * @return mixed|null
         * @throws \ObjectivePHP\Primitives\Exception
         */
        public function getParam($param, $default = null)
        {
            $param = $this->resolveAlias($param);

            return $this->getParams()->fromGet()->get($param, $default);
        }

        /**
         * @return Collection
         */
        public function getParams()
        {
            return $this->getApplication()->getRequest()->getParameters();
        }

        /**
         * @param $params
         *
         * @return $this
         */
        public function setParams($params)
        {

            $this->params = Collection::cast($params);

            return $this;
        }

        /**
         * Return the given service
         *
         * @param $serviceId
         *
         * @return mixed|null
         * @throws \ObjectivePHP\ServicesFactory\Exception
         */
        public function getService($serviceId)
        {
            return $this->getServicesFactory()->get($serviceId);
        }

        /**
         * @return ServicesFactory
         */
        public function getServicesFactory() : ServicesFactory
        {
            return $this->servicesFactory;
        }

        /**
         * @param ServicesFactory $servicesFactory
         *
         * @return $this
         */
        public function setServicesFactory(ServicesFactory $servicesFactory)
        {
            $this->servicesFactory = $servicesFactory;

            return $this;
        }

        /**
         * @return EventsHandler
         */
        public function getEventsHandler()
        {
            return $this->eventsHandler;
        }

        /**
         * @param EventsHandler $eventsHandler
         *
         * @return $this
         */
        public function setEventsHandler($eventsHandler)
        {
            $this->eventsHandler = $eventsHandler;

            return $this;
        }

        /**
         * @param     $url
         * @param int $code
         */
        public function redirect($url, $code = 302)
        {
            header('Location: ' . $url, $code);
            exit;
        }

        /**
         * @param $param
         * @param $alias
         *
         * @return $this
         */
        public function alias($param, $alias)
        {
            $this->aliases[$alias] = $param;

            return $this;
        }

        /**
         * @param $alias
         *
         * @return mixed
         */
        protected function resolveAlias($alias)
        {
            return $this->aliases[$alias] ?? $alias;
        }

        public function getDescription() : string
        {
            return 'Http action class';
        }


        abstract function run(ApplicationInterface $app);

    }
