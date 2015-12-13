<?php

    namespace ObjectivePHP\Application\Action;

    use ObjectivePHP\Application\ApplicationInterface;
    use ObjectivePHP\DataProcessor\DataProcessorInterface;
    use ObjectivePHP\Events\EventsHandler;
    use ObjectivePHP\Primitives\Collection\Collection;
    use ObjectivePHP\Primitives\String\Str;
    use ObjectivePHP\ServicesFactory\ServicesFactory;

    /**
     * Class AbstractAction
     *
     * @package ObjectivePHP\Application\Action
     */
    abstract class AbstractAction implements ActionInterface
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
         * @var Collection
         */
        protected $params;

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
         * @param ApplicationInterface $app
         *
         * @return mixed
         */
        public function __invoke(ApplicationInterface $app)
        {
            $this->setApplication($app);
            $this->setServicesFactory($app->getServicesFactory());
            $this->setEventsHandler($app->getEventsHandler());

            // set params
            $this->params = new Collection();
            $this->setParams(
                $this->getApplication()->getRequest()->getParameters()->fromGet()
            );

            // init action
            $this->init();

            // actually execute action
            return $this->run($app);

        }

        /**
         * @return ApplicationInterface
         */
        public function getApplication()
        {
            return $this->application;
        }

        /**
         * @param ApplicationInterface $application
         *
         * @return $this
         */
        public function setApplication($application)
        {
            $this->application = $application;

            return $this;
        }

        /**
         * @param $param
         * @param $value
         *
         * @return $this
         * @throws \ObjectivePHP\Primitives\Exception
         */
        public function setParam($param, $value)
        {
            $param = $this->resolveAlias($param);
            $this->params->set($param, $value);

            return $this;
        }

        /**
         * @param      $param
         * @param null $default
         *
         * @return mixed|null
         * @throws \ObjectivePHP\Primitives\Exception
         */
        public function getParam($param, $default = null)
        {
            $param = $this->resolveAlias($param);

            return $this->params->get($param, $default);
        }

        /**
         * @return Collection
         */
        public function getParams()
        {
            return $this->params;
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
        public function getServicesFactory()
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

        abstract function run(ApplicationInterface $app);

    }