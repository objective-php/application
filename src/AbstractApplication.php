<?php

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
use ObjectivePHP\ServicesFactory\ServicesFactory;
use ObjectivePHP\Application\AbstractEngine;
<<<<<<< HEAD
=======
use ObjectivePHP\ServicesFactory\ServicesFactoryProviderInterface;
>>>>>>> Integrate new router structure

/**
 * Class AbstractApplication
 *
 * @package ObjectivePHP\Application
 */
abstract class AbstractApplication implements ServicesFactoryProviderInterface
{

    /**
     * @var AbstractEngine
     */
    protected $engine;

    /**
     * @return EventsHandler
     */
    public function getEventsHandler(): EventsHandler
    {
        return $this->getEngine()->getEventsHandler();
    }

    /**
     * @return ServicesFactory
     */
    public function getServicesFactory(): ServicesFactory
    {
        return $this->getEngine()->getServicesFactory();
<<<<<<< HEAD
=======
    }

    public function hasServicesFactory(): bool
    {
        return (bool) $this->getEngine()->getServicesFactory();
>>>>>>> Integrate new router structure
    }


    /**
     * @return string
     */
    public function getEnv(): string
    {
        return $this->getEngine()->getEnv();
    }

    /**
     * @return ClassLoader
     */
    public function getAutoloader(): ClassLoader
    {
        return $this->getEngine()->getAutoloader();
    }



    /**
     * @return Config
     */
    public function getConfig(): ConfigInterface
    {
        return $this->getEngine()->getConfig();
    }

    /**
     * @return Collection
     */
    public function getPackages(): Collection
    {
        return $this->getEngine()->getPackages();
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
     * @return AbstractEngine
     */
    public function getEngine(): AbstractEngine
    {
        return $this->engine;
    }

    /**
     * @param AbstractEngine $engine
     * @return AbstractApplication
     */
    public function setEngine(AbstractEngine $engine): AbstractApplication
    {
        $this->engine = $engine;
        return $this;
    }

    public function getDirectives() : array
    {
        return [];
    }

    public function getParameters() : array
    {
        return [];
    }

    public function hasConfig(): bool
    {
        return $this->getEngine()->hasConfig();
    }
}
