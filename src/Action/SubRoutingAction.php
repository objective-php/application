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

/**
 * Class SubRoutingAction
 * @package ObjectivePHP\Application\Action
 */
abstract class SubRoutingAction extends HttpAction
{

    /**
     * @var
     */
    protected $middlewareStack;

    /**
     * @param ApplicationInterface $app
     * @return mixed
     * @throws Exception
     */
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

        // TODO fix http return code (probably 405) 
        if (!is_callable($middleware)) {
            throw new Exception(sprintf('No middleware matching routed reference "%s" has been registered',
                $middlewareReference));
        }

        return $middleware($app);

    }


    /**
     * @return mixed
     */
    abstract public function route();

    /**
     * @param $reference
     * @param $middleware
     * @return $this
     */
    public function registerMiddleware($reference, $middleware)
    {


        $middleware = ($middleware instanceof MiddlewareInterface) ? $middleware : new EmbeddedMiddleware($middleware);

        $this->getMiddlewareStack()[$reference] = $middleware;

        return $this;

    }

    /**
     * @param $reference
     * @return mixed
     */
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
