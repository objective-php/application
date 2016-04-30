<?php
/**
 * Created by PhpStorm.
 * User: gauthier
 * Date: 23/04/2016
 * Time: 09:17
 */

namespace ObjectivePHP\Application\Action;


use ObjectivePHP\Application\ApplicationInterface;
use ObjectivePHP\Application\Middleware\AbstractMiddleware;
use ObjectivePHP\Application\Middleware\EmbeddedMiddleware;
use ObjectivePHP\Application\Middleware\Exception;
use ObjectivePHP\Application\Middleware\MiddlewareInterface;
use ObjectivePHP\Primitives\Collection\Collection;

abstract class SubRoutingAction extends AbstractMiddleware
{

    protected $middlewareStack;

    public function __invoke(...$args)
    {
        $this->setApplication($args[0]);
        return $this->run($args[0]);
    }

    public function run(ApplicationInterface $app)
    {
        $middlewareReference = $this->route();

        $middleware = $this->getMiddleware($middlewareReference);

        if(!is_callable($middleware))
        {
            throw new Exception(sprintf('No middleware matching routed reference "%s" has been registered', $middlewareReference));
        }

        return $middleware($app);

    }


    abstract public function route();

    public function registerMiddleware($reference, $middleware)
    {


        $middleware = ($middleware instanceof MiddlewareInterface) ? $middleware  : new EmbeddedMiddleware($middleware);

        $this->getMiddlewareStack()[$reference] = $middleware;

        return $this;

    }

    public function getMiddleware($reference)
    {
        return $this->getMiddlewareStack()->get($reference);
    }

    /**
     * @return mixed
     */
    public function getMiddlewareStack()
    {
        if(is_null($this->middlewareStack))
        {
            $this->middlewareStack = (new Collection())->restrictTo(MiddlewareInterface::class);
        }

        return $this->middlewareStack;
    }


}