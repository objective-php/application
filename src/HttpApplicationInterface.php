<?php
/**
 * Created by PhpStorm.
 * User: gde
 * Date: 15/05/2018
 * Time: 11:57
 */

namespace ObjectivePHP\Application;

use ObjectivePHP\Application\Middleware\MiddlewareRegistry;
use ObjectivePHP\Router\RouterInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class AbstractApplication
 *
 * @package ObjectivePHP\Application
 */
interface HttpApplicationInterface extends RequestHandlerInterface, ApplicationInterface
{
    /**
     * @return bool
     */
    public function hasRequest(): bool;

    /**
     * @return ServerRequestInterface
     */
    public function getRequest(): ServerRequestInterface;

    /**
     * @return RouterInterface
     */
    public function getRouter(): RouterInterface;

    /**
     * @return MiddlewareRegistry
     */
    public function getMiddlewares(): MiddlewareRegistry;

    /**
     * @return MiddlewareRegistry
     */
    public function getExceptionHandlers(): MiddlewareRegistry;
}
