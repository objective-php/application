<?php
/**
 * Created by PhpStorm.
 * User: gde
 * Date: 15/05/2018
 * Time: 12:03
 */

namespace ObjectivePHP\Application;


use Composer\Autoload\ClassLoader;
use ObjectivePHP\Application\Package\PackageInterface;
use ObjectivePHP\Application\Workflow\WorkflowEvent;
use ObjectivePHP\Config\Config;
use ObjectivePHP\Config\ConfigAccessorsTrait;
use ObjectivePHP\Config\ConfigInterface;
use ObjectivePHP\Events\EventsHandler;
use ObjectivePHP\Filter\FiltersProviderInterface;
use ObjectivePHP\Primitives\Collection\Collection;
use ObjectivePHP\Router\RouterInterface;
use ObjectivePHP\ServicesFactory\ServicesFactory;

class AbstractApplication
{

    use ConfigAccessorsTrait;

    /**
     * @var ServicesFactory
     */
    protected $servicesFactory;

    /** @var string */
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
    public function setEnv($env): ApplicationInterface
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
}