<?php
    /**
     * Created by PhpStorm.
     * User: gauthier
     * Date: 08/12/2015
     * Time: 08:52
     */
    
    namespace ObjectivePHP\Application\Operation\Common;
    
    
    use ObjectivePHP\Application\ApplicationInterface;
    use ObjectivePHP\Application\Config\Route;
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
            $routes = $app->getConfig()->subset(Route::class);
            if ($routes)
            {
                $path = $routes[$path] ?? $path;
            }

            // inject route
            $app->getRequest()->setRoute($path);

        }

    }
