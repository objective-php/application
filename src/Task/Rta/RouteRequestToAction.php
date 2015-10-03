<?php
    namespace ObjectivePHP\Application\Task\Rta;


    use ObjectivePHP\Application\ApplicationInterface;
    use ObjectivePHP\Application\Workflow\Event\WorkflowEvent;
    use ObjectivePHP\Events\Callback\AliasedCallback;
    use ObjectivePHP\Primitives\Collection\Collection;
    use ObjectivePHP\Primitives\String\String;
    use ObjectivePHP\Application\Exception;
    use ObjectivePHP\ServicesFactory\Reference;

    class RouteRequestToAction
    {
        /**
         * @var ApplicationInterface
         */
        protected $application;

        public function __invoke(WorkflowEvent $event)
        {

            $this->application = $application = $event->getApplication();

            $path = $event->getApplication()->getRequest()->getUri()->getPath();


            $actionClass = $this->resolveActionClassName($path);

            $action = $this->resolveActionFullyQualifiedName($actionClass);

            if(!$action)
            {
                throw new Exception(sprintf('No callback found to map the requested action "%s"', $path), Exception::ACTION_NOT_FOUND);
            }

            // if action is a class, register it as service
            if(is_string($action) && class_exists($action))
            {
                $serviceId = $this->computeServiceName($path);
                $this->application->getServicesFactory()->registerService(['id' => $serviceId, 'class' => $action]);

                // replace action by serviceId to ensure it will be fetched using the ServicesFactory
                $action = new Reference($serviceId);
            }

            $application->getWorkflow()->bind('run.execute', new AliasedCallback('action', $action));

            // store action in event result for further reference
            return $action;
        }

        /**
         * @param $path
         *
         * @return string
         */
        protected function resolveActionClassName($path)
        {
            // check if path is routed
            if ($this->application->getConfig()->has('router.routes'))
            {
                $routes = Collection::cast($this->application->getConfig()->get('router.routes'));

                if($action = $routes->get($path)) return $action;
            }

            // clean path name
            $path = String::cast($path);
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
            $registeredActionNamespaces = array_reverse(Collection::cast($this->application->getConfig()->actions->namespaces)
                                                                  ->toArray());

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
            return (string) String::cast($path)->trim('/')->replace('/', '.')->prepend('action.');
        }
    }