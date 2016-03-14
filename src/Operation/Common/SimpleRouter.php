<?php
    /**
     * Created by PhpStorm.
     * User: gauthier
     * Date: 08/12/2015
     * Time: 08:52
     */
    
    namespace ObjectivePHP\Application\Operation\Common;
    
    
    use ObjectivePHP\Application\ApplicationInterface;
    use ObjectivePHP\Application\Config\SimpleRoute;
    use ObjectivePHP\Application\Config\UrlAlias;
    use ObjectivePHP\Application\Middleware\AbstractMiddleware;
    use ObjectivePHP\Primitives\Collection\Collection;

    /**
     * Class SimpleRouter
     *
     * This very basic router just maps the current URL to the route
     *
     * @package ObjectivePHP\Application\Operation\Common
     */
    class SimpleRouter extends AbstractMiddleware
    {
        /**
         * @param ApplicationInterface $application
         *
         * @return mixed
         */
        public function run(ApplicationInterface $app)
        {

            $path = rtrim($app->getRequest()->getUri()->getPath(), '/');

            // default to home
            if (!$path)
            {
                $path = '/';
            }

            // check if path is routed
            $aliases = $app->getConfig()->subset(UrlAlias::class);
            if ($aliases)
            {
                $path = $aliases[$path] ?? $path;
            }

            // look for matching route
            $routes = $app->getConfig()->subset(SimpleRoute::class)->reverse();
            /** @var SimpleRoute $route */
            $routed = false;
            foreach($routes as $alias => $route)
            {
                if($route->matches($app->getRequest()))
                {
                    $app->getRequest()->setAction($route->getAction());
                    $app->getRequest()->setRoute($alias);
                    $routed = true;
                    break;
                }
            }

            // inject route if none matched
            if(!$routed) {
                $app->getRequest()->setRoute($path);
            }

        }

    }
