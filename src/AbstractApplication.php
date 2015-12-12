<?php

    namespace ObjectivePHP\Application;
    
    use Composer\Autoload\ClassLoader;
    use ObjectivePHP\Application\Workflow\Hook;
    use ObjectivePHP\Application\Workflow\Step;
    use ObjectivePHP\Config\Config;
    use ObjectivePHP\Config\Loader\DirectoryLoader;
    use ObjectivePHP\Events\EventsHandler;
    use ObjectivePHP\Matcher\Matcher;
    use ObjectivePHP\Message\Request\RequestInterface;
    use ObjectivePHP\Message\Response\ResponseInterface;
    use ObjectivePHP\Primitives\Collection\Collection;
    use ObjectivePHP\ServicesFactory\ServiceReference;
    use ObjectivePHP\ServicesFactory\ServicesFactory;
    use Zend\Diactoros\Response;

    /**
     * Class AbstractApplication
     *
     * @package ObjectivePHP\Application
     */
    abstract class AbstractApplication implements ApplicationInterface
    {
        /**
         * @var EventsHandler
         */
        protected $eventsHandler;

        /**
         * @var ServicesFactory
         */
        protected $servicesFactory;

        /**
         * @var ClassLoader
         */
        protected $autoloader;

        /**
         * @var string
         */
        protected $env;

        /**
         * @var Config
         */
        protected $config;

        /**
         * @var RequestInterface
         */
        protected $request;

        /**
         * @var ResponseInterface
         */
        protected $response;


        /**
         * @var Collection
         */
        protected $steps;

        /**
         * @var Collection
         */
        protected $params;

        /**
         * @var array
         */
        protected $executionTrace = [];

        /**
         * @var
         */
        protected $currentExecutionStack;

        /**
         * @var Matcher
         */
        protected $routeMatcher;

        /**
         * AbstractApplication constructor.
         *
         * @param ClassLoader|null $autoloader
         */
        public function __construct(ClassLoader $autoloader = null)
        {
            if ($autoloader) $this->setAutoloader($autoloader);
            $this->steps        = (new Collection())->restrictTo(Step::class);
            $this->params       = new Collection();
            $this->routeMatcher = (new Matcher())->setSeparator('/');

            $this->init();


        }

        /**
         * @param array $steps
         */
        public function addSteps(string ...$steps)
        {
            foreach ($steps as $step)
            {
                $this->steps->set($step, new Step($step));
            }

            return $this;
        }

        /**
         * @return string
         */
        public function getEnv()
        {
            return $this->env;
        }

        /**
         * @param string $env
         *
         * @return $this
         */
        public function setEnv($env)
        {
            $this->env = $env;

            return $this;
        }

        /**
         *
         */
        public function loadConfig($path)
        {
            $configLoader = new DirectoryLoader();

            $this->config = $configLoader->load($path);
        }

        /**
         * @return Config
         */
        public function getConfig()
        {
            return $this->config;
        }

        /**
         * @param Config $config
         *
         * @return $this
         */
        public function setConfig($config)
        {
            $this->config = $config;

            return $this;
        }

        /**
         * @return EventsHandler
         */
        public function getEventsHandler()
        {

            if (is_null($this->eventsHandler))
            {
                $this->eventsHandler = new EventsHandler();
            }

            return $this->eventsHandler;
        }

        /**
         * @param EventsHandler $eventsHandler
         *
         * @return $this
         */
        public function setEventsHandler(EventsHandler $eventsHandler)
        {
            $this->eventsHandler = $eventsHandler;

            return $this;
        }

        /**
         * @return RequestInterface
         */
        public function getRequest()
        {
            return $this->request;
        }

        /**
         * @param RequestInterface $request
         *
         * @return $this
         */
        public function setRequest(RequestInterface $request)
        {
            $this->request = $request;

            return $this;
        }

        /**
         * @return ResponseInterface
         */
        public function getResponse()
        {
            return $this->response;
        }

        /**
         * @param Response $response
         *
         * @return $this
         */
        public function setResponse(Response $response)
        {
            $this->response = $response;

            return $this;
        }

        /**
         * @return ServicesFactory
         */
        public function getServicesFactory()
        {
            if (is_null($this->servicesFactory))
            {
                $this->servicesFactory = new ServicesFactory();
            }

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
         * @return Collection
         */
        public function getSteps() : Collection
        {
            return $this->steps;
        }

        /**
         * @param $step
         *
         * @return Step
         * @throws \ObjectivePHP\Primitives\Exception
         */
        public function on($step) : Step
        {
            $stepInstance = $this->steps->get($step);

            if (!$stepInstance)
            {
                throw new Exception(sprintf('Unknown step "%s". Please add this step before trying to plug middleware on it', $step));
            }

            return $stepInstance;
        }

        /**
         * @throws \Throwable
         */
        public function run()
        {
            // let ServicesFactory and EventsHandler know each other
            $this->getEventsHandler()->setServicesFactory($this->getServicesFactory());
            $this->getServicesFactory()->setEventsHandler($this->getEventsHandler());

            $executionTrace = [];
            try
            {
                $this->steps->each(function (Step $step) use (&$executionTrace)
                {
                    $this->getEventsHandler()->trigger('application.workflow.step.run', $step);
                    $this->executionTrace[$step->getName()] = [];
                    $this->currentExecutionStack            = &$this->executionTrace[$step->getName()];

                    $step->each(function (Hook $hook)
                    {
                        $hook->run($this);
                    }
                    );
                });
            } catch (\Throwable $e)
            {
                throw $e;
            }
        }

        /**
         * @return ClassLoader
         */
        public function getAutoloader()
        {
            return $this->autoloader;
        }

        /**
         * @param ClassLoader $autoloader
         *
         * @return $this
         */
        public function setAutoloader(ClassLoader $autoloader)
        {
            $this->autoloader = $autoloader;

            return $this;
        }

        /**
         * @return Collection
         */
        public function getParams()
        {
            return $this->params;
        }

        /**
         * @param Collection $params
         *
         * @return $this
         */
        public function setParams($params)
        {
            $this->params = $params;

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
            return $this->params->get($param, $default);
        }


        /**
         * @param $param
         * @param $value
         *
         * @throws \ObjectivePHP\Primitives\Exception
         */
        public function setParam($param, $value)
        {
            $this->params->set($param, $value);
        }

        /**
         * @return Matcher
         */
        public function getRouteMatcher()
        {
            return $this->routeMatcher;
        }

        /**
         * @param Matcher $routeMatcher
         *
         * @return $this
         */
        public function setRouteMatcher($routeMatcher)
        {
            $this->routeMatcher = $routeMatcher;

            return $this;
        }

    }