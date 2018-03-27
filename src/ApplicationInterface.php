<?php

namespace ObjectivePHP\Application;

use Composer\Autoload\ClassLoader;
use ObjectivePHP\Config\Config;
use ObjectivePHP\Config\ConfigInterface;
use ObjectivePHP\Events\EventsHandler;
use ObjectivePHP\ServicesFactory\ServicesFactory;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Interface ApplicationInterface
 *
 * @package ObjectivePHP\Application
 */
interface ApplicationInterface extends RequestHandlerInterface
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
     * @param ServerRequestInterface $request
     *
     * @return ApplicationInterface
     */
    public function setRequest(ServerRequestInterface $request): ApplicationInterface;

    /**
     * @return ServerRequestInterface
     */
    public function getRequest(): ServerRequestInterface;

    /**
     * @return string
     */
    public function getEnv(): string;

    /**
     * @return ClassLoader
     */
    public function getAutoloader(): ClassLoader;
}
