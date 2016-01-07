<?php
    namespace ObjectivePHP\Application\Operation\Rta;


    use ObjectivePHP\Application\ApplicationInterface;
    use ObjectivePHP\Application\Config\ActionNamespace;
    use ObjectivePHP\Application\Middleware\AbstractMiddleware;
    use ObjectivePHP\Application\Middleware\ActionMiddleware;
    use ObjectivePHP\Application\Workflow\Event\WorkflowEvent;
    use ObjectivePHP\Events\Callback\AliasedCallback;
    use ObjectivePHP\Primitives\Collection\Collection;
    use ObjectivePHP\Primitives\String\Str;
    use ObjectivePHP\Application\Exception;
    use ObjectivePHP\ServicesFactory\ServiceReference;

    /**
     * Class ActionRunner
     *
     * @package ObjectivePHP\Application\Task\Rta
     */
    class ActionPlugger extends AbstractMiddleware
    {
        /**
         * @var ApplicationInterface
         */
        protected $application;

        /**
         * @param ApplicationInterface $app
         *
         * @throws Exception
         * @throws \ObjectivePHP\ServicesFactory\Exception
         */
        public function run(ApplicationInterface $app)
        {

            $this->application = $app;

            $route = $app->getRequest()->getRoute();

            // compute service id
            $serviceId = $this->computeServiceName($route);

            // if no service matching the route has been registered,
            // try to locate a class that could be used as service
            if(!$app->getServicesFactory()->isServiceRegistered($serviceId))
            {
                $actionClass = $this->resolveActionClassName($route);

                $action = $this->resolveActionFullyQualifiedName($actionClass);

                if (!$action)
                {
                    throw new Exception(sprintf('No callback found to map the requested route "%s"', $route), Exception::ACTION_NOT_FOUND);
                }

                $app->getServicesFactory()->registerService(['id' => $serviceId, 'class' => $action]);
            }

            // replace action by serviceId to ensure it will be fetched using the ServicesFactory
            $actionReference = new ServiceReference($serviceId);

            // wrap action to inject returned value in application
            $app->getStep('action')->plug($actionMiddleware = new ActionMiddleware($actionReference));


            // store action as application parameter for further reference
            $app->setParam('runtime.action.middleware', $actionMiddleware);
            $app->setParam('runtime.action.service-id', $serviceId);
        }

        /**
         * @param $path
         *
         * @return string
         */
        protected function resolveActionClassName($path)
        {

            // clean path name
            $path = Str::cast($path);
            $path->trim('/');

            $namespaces = $path->split('/');

            $namespaces->each(function(&$namespace)
            {
                $parts = explode('-', $namespace);
                array_walk($parts, function (&$part)
                {
                    $part = ucfirst($part);
                });

               $namespace = implode('', $parts);
            });

            $backslash = '\\';

            $className = str_replace('\\\\', '\\', implode($backslash, $namespaces->toArray()));

            return $className;
        }

        /**
         * @param $className
         *
         * @return null|string
         */
        public function resolveActionFullyQualifiedName($className)
        {
            $registeredActionNamespaces = $this->application->getConfig()->get(ActionNamespace::DIRECTIVE);

            foreach ($registeredActionNamespaces as $namespace)
            {
                $fullClassName = $namespace . '\\' . $className;
                if (class_exists('\\' . $fullClassName))
                {
                    return $fullClassName;
                }
            }

            return null;
        }

        /**
         * Return normalized service name reflecting path
         *
         * This will use to auto-register action as a service
         *
         * @param $path
         */
        protected function computeServiceName($path)
        {
            return (string) Str::cast($path)->trim('/')->replace('/', '.')->prepend('action.');
        }
    }
