<?php


namespace ObjectivePHP\Application;


use Composer\Autoload\ClassLoader;
use ObjectivePHP\Application\Package\PackageInterface;
use ObjectivePHP\Application\Workflow\PackagesInitListener;
use ObjectivePHP\Application\Workflow\PackagesReadyListener;
use ObjectivePHP\Application\Workflow\WorkflowEvent;
use ObjectivePHP\Config\Config;
use ObjectivePHP\Config\ConfigAccessorsTrait;
use ObjectivePHP\Config\ConfigProviderInterface;
use ObjectivePHP\Config\Directive\DirectiveInterface;
use ObjectivePHP\Config\DirectivesProviderInterface;
use ObjectivePHP\Config\Loader\FileLoader\FileLoader;
use ObjectivePHP\Config\ParametersProviderInterface;
use ObjectivePHP\Events\EventsHandler;
use ObjectivePHP\Filter\FiltersProviderInterface;
use ObjectivePHP\Primitives\Collection\Collection;
use ObjectivePHP\ServicesFactory\Config\ServiceDefinition;
use ObjectivePHP\ServicesFactory\ServicesFactory;
use ReflectionObject;

abstract class AbstractEngine implements DirectivesProviderInterface, ParametersProviderInterface
{

    use ConfigAccessorsTrait;

    /**
     * @var ClassLoader
     */
    protected $autoloader;


    /**
     * @var Collection
     */
    protected $packages;

    /**
     * @var string
     */
    protected $env;

    /**
     * @var string
     */
    protected $httpApplication;

    /**
     * @var string
     */
    protected $cliApplication;

    /**
     * @var ApplicationInterface
     */
    protected $application;

    /**
     * @var ServicesFactory
     */
    protected $servicesFactory;

    /**
     * @var EventsHandler
     */
    protected $eventsHandler;

    /**
     * @var string
     */
    protected $projectNamespace;


    /**
     * @var string
     */
    protected $configPath = 'app/config';


    /**
     * AbstractEngine constructor.
     * @param ClassLoader $autoloader
     * @param Collection $packages
     * @param string $env
     */
    public function __construct(string $env, ClassLoader $autoloader)
    {
        $this->autoloader = $autoloader;
        $this->packages = (new Collection())->restrictTo(PackageInterface::class);
        $this->env = $env;

        $this->servicesFactory = new ServicesFactory();
        $this->eventsHandler = new EventsHandler();
        $this->config = new Config();
        $this->config->registerDirective(new ServiceDefinition);
        $this->servicesFactory->setConfig($this->config);


        // register default local packages storage
        $reflectionObject = new \ReflectionObject($this);
        $this->getAutoloader()->addPsr4($reflectionObject->getNamespaceName() . '\\Package\\', 'app/packages/');

    }

    abstract public function init();

    public function run()
    {
        $cli = php_sapi_name() === 'cli';

        $applicationClass = $cli ? $this->cliApplication : $this->httpApplication;

        $application = new $applicationClass($this);
        $this->setApplication($application);
        $this->servicesFactory->registerRawService(['id' => 'application', 'instance' => $application]);
        $this->triggerWorkflowEvent(WorkflowEvent::BOOTSTRAP_DONE);

        $this->init();

        /** @var PackageInterface $package */
        foreach ($this->packages as $package) {

            // inject dependencies if needed
            $this->getServicesFactory()->injectDependencies($package);

            if ($package instanceof FiltersProviderInterface) {
                if (!$package->getFilterEngine()->filter($this)) {
                    continue;
                }
            }

            if ($package instanceof ConfigProviderInterface) {
                $this->getConfig()->merge($package->getConfig());
            }

            if ($package instanceof DirectivesProviderInterface) {
                $this->getConfig()->registerDirective(...$package->getDirectives());
            }

            if ($package instanceof ParametersProviderInterface) {
                $this->getConfig()->hydrate($package->getParameters());
            }


            if ($package instanceof PackagesInitListener) {
                $this->getEventsHandler()->bind(WorkflowEvent::PACKAGES_INIT, [$package, 'onPackagesInit']);
            }

            if ($package instanceof PackagesReadyListener) {
                $this->getEventsHandler()->bind(WorkflowEvent::PACKAGES_READY, [$package, 'onPackagesReady']);
            }
        }

        // register default configuration directives
        $this->getConfig()->registerDirective(...$application->getDirectives());

        // load default configuration parameters
        $this->getConfig()->hydrate($this->getParameters());

        // read configuration
        if (is_dir($this->getConfigPath())) {
            $this->getConfig()->hydrate((new FileLoader())->load($this->getConfigPath()));
        }

        $this->triggerWorkflowEvent(WorkflowEvent::PACKAGES_INIT);


        // load services
        /** @var ServiceDefinition[] $servicesDefinitions */
        $servicesDefinitions = $this->getConfig()->getRaw(ServiceDefinition::KEY);

        foreach ($servicesDefinitions as $id => $servicesDefinition) {
            $service = array_merge(['id' => $id], $servicesDefinition->getSpecifications());
            $this->getServicesFactory()->registerRawService($service);
        }

        $this->triggerWorkflowEvent(WorkflowEvent::PACKAGES_READY);

        $application->run();
    }


    /**
     * @param PackageInterface $package
     * @param array ...$filters
     */
    public function registerPackage(PackageInterface $package, ...$filters)
    {
        // register package autoload
        $reflectionObject = new ReflectionObject($package);
        $this->getAutoloader()->addPsr4(
            $reflectionObject->getNamespaceName() . '\\',
            dirname($reflectionObject->getFileName()) . '/src'
        );

        if ($package instanceof FiltersProviderInterface) {
            $package->getFilterEngine()->registerFilter(...$filters);
        }

        $this->packages->append($package);
    }

    /**
     * @param       $eventName
     * @param null $origin
     * @param array $context
     *
     * @throws \ObjectivePHP\Events\Exception\EventException
     * @throws \ObjectivePHP\Primitives\Exception
     * @throws \ObjectivePHP\ServicesFactory\Exception\ServiceNotFoundException
     */
    public function triggerWorkflowEvent($eventName, $origin = null, $context = [])
    {
        $this->getEventsHandler()->trigger($eventName, $origin, $context, new WorkflowEvent($this->getApplication()));
    }

    /**
     * @return string
     */
    public function getEnv(): string
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
     * @return ClassLoader
     */
    public function getAutoloader(): ClassLoader
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

    public function registerHttpApplication(string $class)
    {
        $this->httpApplication = $class;

        return $this;
    }

    public function registerCliApplication(string $class)
    {
        $this->cliApplication = $class;

        return $this;
    }

    /**
     * @return ServicesFactory
     */
    public function getServicesFactory(): ServicesFactory
    {
        return $this->servicesFactory;
    }

    /**
     * @param ServicesFactory $servicesFactory
     * @return AbstractEngine
     */
    public function setServicesFactory(ServicesFactory $servicesFactory): AbstractEngine
    {
        $this->servicesFactory = $servicesFactory;
        return $this;
    }

    /**
     * @return EventsHandler
     */
    public function getEventsHandler(): EventsHandler
    {
        return $this->eventsHandler;
    }

    /**
     * @param EventsHandler $eventsHandler
     * @return AbstractEngine
     */
    public function setEventsHandler(EventsHandler $eventsHandler): AbstractEngine
    {
        $this->eventsHandler = $eventsHandler;
        return $this;
    }

    /**
     * @return string
     */
    public function getProjectNamespace()
    {
        if (is_null($this->projectNamespace)) {
            $this->projectNamespace = (new ReflectionObject($this))->getNamespaceName();
        }

        return $this->projectNamespace;
    }

    /**
     * @return string
     */
    public function getConfigPath(): string
    {
        return $this->configPath;
    }

    /**
     * @param string $configPath
     *
     * @return $this
     */
    public function setConfigPath(string $configPath): self
    {
        $this->configPath = $configPath;

        return $this;
    }

    public function getDirectives(): array
    {
        return [];
    }

    public function getParameters(): array
    {
        return [];
    }

    /**
     * @return Config
     */
    public function getConfig(): Config
    {
        return $this->config;
    }

    /**
     * @return ApplicationInterface
     */
    public function getApplication(): ApplicationInterface
    {
        return $this->application;
    }

    /**
     * @param ApplicationInterface $application
     * @return AbstractEngine
     */
    public function setApplication(ApplicationInterface $application): AbstractEngine
    {
        $this->application = $application;
        return $this;
    }


}