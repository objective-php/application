<?php

namespace ObjectivePHP\Application;

use ObjectivePHP\Application\Exception\ExceptionHandlerInterface;
use ObjectivePHP\Config\ConfigInterface;
use ObjectivePHP\Events\EventsHandlerInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
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
     * @return EventsHandlerInterface
     */
    public function getEventsHandler(): EventsHandlerInterface;

    /**
     * @return ContainerInterface
     */
    public function getServicesFactory(): ContainerInterface;

    /**
     * @return ConfigInterface
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
     * @param ResponseInterface $response
     *
     * @return ApplicationInterface
     */
    public function setResponse(ResponseInterface $response): ApplicationInterface;

    /**
     * @return ResponseInterface
     */
    public function getResponse(): ResponseInterface;

    /**
     * @param ExceptionHandlerInterface $exceptionHandler
     * @return ApplicationInterface
     */
    public function setExceptionHandler(ExceptionHandlerInterface $exceptionHandler): ApplicationInterface;

    /**
     * @return ExceptionHandlerInterface
     */
    public function getExceptionHandler(): ExceptionHandlerInterface;
}
