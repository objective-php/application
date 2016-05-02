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
use ObjectivePHP\Invokable\Invokable;
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
        // auto inject dependencies
        $servicesFactory = $app->getServicesFactory();

        if ($servicesFactory)
        {
            $normalizedMiddleware = null;
            switch (true) {

                // middlewares can be an array containing [$object, 'method']
                case is_array($middleware) && !empty($middleware[0]) && is_object($middleware[0]):
                    $normalizedMiddleware = $middleware[0];
                    break;

                case $middleware instanceof Invokable:
                    $normalizedMiddleware = &$middleware->getCallable();
                    break;

                case is_object($middleware):
                    $normalizedMiddleware = $middleware;
                    break;
            }

            if($normalizedMiddleware)
            {
                $servicesFactory->injectDependencies($normalizedMiddleware);
            }
        }

        if (!is_callable($middleware)) {
            throw new Exception(sprintf('No middleware matching routed reference "%s" has been registered',
                $middlewareReference));
        }

        return $middleware($app);

    }


    abstract public function route();

    public function registerMiddleware($reference, $middleware)
    {


        $middleware = ($middleware instanceof MiddlewareInterface) ? $middleware : new EmbeddedMiddleware($middleware);

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
        if (is_null($this->middlewareStack)) {
            $this->middlewareStack = (new Collection())->restrictTo(MiddlewareInterface::class);
        }

        return $this->middlewareStack;
    }


}
