<?php

namespace ObjectivePHP\Application;

use Composer\Autoload\ClassLoader;
use ObjectivePHP\Config\Config;
use ObjectivePHP\Config\ConfigInterface;
use ObjectivePHP\Config\ConfigProviderInterface;
use ObjectivePHP\Events\EventsHandler;
use ObjectivePHP\Primitives\Collection\Collection;
use ObjectivePHP\Router\RouterInterface;
use ObjectivePHP\ServicesFactory\ServicesFactory;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Interface ApplicationInterface
 *
 * @package ObjectivePHP\Application
 */
interface ApplicationInterface extends ConfigProviderInterface
{

    /**
     * @return mixed
     */
    public function init();

    /**
     * @return mixed
     */
    public function run();

    /**
     * @return EventsHandler
     */
    public function getEventsHandler(): EventsHandler;

    /**
     * @return ServicesFactory
     */
    public function getServicesFactory(): ServicesFactory;

    /**
     * @return Config
     */
    public function getConfig(): ConfigInterface;

    /**
     * @return string
     */
    public function getEnv(): string;

    /**
     * @return ClassLoader
     */
    public function getAutoloader(): ClassLoader;

    /**
     * @return Collection
     */
    public function getPackages(): Collection;
}
