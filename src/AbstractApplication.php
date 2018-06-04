<?php

namespace ObjectivePHP\Application;

use Composer\Autoload\ClassLoader;
use ObjectivePHP\Application\Injector\DefaultInjector;
use ObjectivePHP\Application\Package\PackageInterface;
use ObjectivePHP\Application\Workflow\WorkflowEvent;
use ObjectivePHP\Config\Config;
use ObjectivePHP\Config\ConfigAccessorsTrait;
use ObjectivePHP\Config\ConfigInterface;
use ObjectivePHP\Events\EventsHandler;
use ObjectivePHP\Filter\FiltersProviderInterface;
use ObjectivePHP\Primitives\Collection\Collection;
use ObjectivePHP\ServicesFactory\ServicesFactory;
use ObjectivePHP\ServicesFactory\Specification\PrefabServiceSpecification;

/**
 * Class AbstractApplication
 *
 * @package ObjectivePHP\Application
 */
abstract class AbstractApplication
{
    use ConfigAccessorsTrait;

    /**
     * @var ServicesFactory
     */
    protected $servicesFactory;

    /**
     * @var string
     */
    protected $projectNamespace;

    /**
     * @var ClassLoader
     */
    protected $autoloader;

    /**
     * @var EventsHandler
     */
    protected $eventsHandler;

    /**
     * @var string
     */
    protected $env;

    /**
     * @var Collection
     */
    protected $packages;

    /**
     * AbstractApplication constructor.
     * @throws \ObjectivePHP\Primitives\Exception
     * @throws \ObjectivePHP\ServicesFactory\Exception\ServicesFactoryException
     * @throws \ObjectivePHP\Config\Exception\ConfigException
     */
    public function __construct(ClassLoader $autoloader = null)
    {
        $buffer = $this->cleanBuffer();

        ob_start();
        if ($buffer) {
            echo $buffer;
        }

        if ($autoloader) {
            // register packages autoloading
            $this->setAutoloader($autoloader);
            // register default local packages storage
            $reflectionObject = new \ReflectionObject($this);
            $this->getAutoloader()->addPsr4($reflectionObject->getNamespaceName() . '\\Package\\', 'packages/');
        }

        $this->packages = (new Collection())->restrictTo(PackageInterface::class);

        $this->eventsHandler = new EventsHandler();

        // register default configuration directives
        $this->getConfig()->registerDirective(...$this->getConfigDirectives());

        // load default configuration parameters
        $this->getConfig()->hydrate($this->getConfigParams());

        $this->servicesFactory = (new ServicesFactory())
            ->registerService(new PrefabServiceSpecification('application', $this));

        // register application in services factory
        $this->getServicesFactory()->setConfig($this->getConfig());

        // register default injector
        $this->getServicesFactory()->registerInjector(new DefaultInjector());

        // let ServicesFactory and EventsHandler know each other
        $this->getEventsHandler()->setServicesFactory($this->getServicesFactory());

        // initialize application by plugging middlewares
        $this->init();
    }

    /**
     * Delegated constructor
     * Implement this method in your own Application class
     */
    abstract public function init();

    /**
     * @param PackageInterface $package
     * @param array ...$filters
     */
    public function registerPackage(PackageInterface $package, ...$filters)
    {
        // register package autoload
        $reflectionObject = new \ReflectionObject($package);
        $this->getAutoloader()->addPsr4(
            $reflectionObject->getNamespaceName() . '\\',
            dirname($reflectionObject->getFileName()) . '/src'
        );

        if ($package instanceof FiltersProviderInterface && $filters) {
            $package->getFilterEngine()->registerFilter(...$filters);
        }

        $this->packages->append($package);
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
     *
     * @return $this
     */
    public function setEventsHandler(EventsHandler $eventsHandler): ApplicationInterface
    {
        $this->eventsHandler = $eventsHandler;

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
     *
     * @return $this
     */
    public function setServicesFactory(ServicesFactory $servicesFactory)
    {
        $this->servicesFactory = $servicesFactory;

        return $this;
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

    /**
     * @return Config
     */
    public function getConfig(): ConfigInterface
    {
        // init Config
        if (is_null($this->config)) {
            $this->config = new Config();
        }

        return $this->config;
    }

    /**
     * @return Collection
     */
    public function getPackages(): Collection
    {
        return $this->packages;
    }

    /**
     * @param Collection $packages
     */
    public function setPackages(Collection $packages)
    {
        $this->packages = $packages;
    }

    /**
     * @return string
     */
    protected function getProjectNamespace()
    {
        if (is_null($this->projectNamespace)) {
            $this->projectNamespace = (new \ReflectionObject($this))->getNamespaceName();
        }

        return $this->projectNamespace;
    }

    /**
     * Clean and return buffer
     *
     * @return string
     */
    protected function cleanBuffer(): string
    {
        $buffer = '';
        while (ob_get_level() > 0) {
            $buffer .= ob_get_clean();
        }

        return $buffer;
    }

    /**
     * @param       $eventName
     * @param null  $origin
     * @param array $context
     *
     * @throws \ObjectivePHP\Events\Exception\EventException
     * @throws \ObjectivePHP\Primitives\Exception
     * @throws \ObjectivePHP\ServicesFactory\Exception\ServiceNotFoundException
     */
    protected function triggerWorkflowEvent($eventName, $origin = null, $context = [])
    {
        $this->getEventsHandler()->trigger($eventName, $origin, $context, new WorkflowEvent($this));
    }

    /**
     * @return array
     */
    protected function getConfigDirectives(): array
    {
        return [];
    }

    /**
     * @return array
     */
    protected function getConfigParams(): array
    {
        return [];
    }
}
