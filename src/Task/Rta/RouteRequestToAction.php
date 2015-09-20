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


            if($alias = $this->resolveAlias($path))
            {
                $path = $alias;
            }

            $action = $this->resolveActionClassName($path);

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

        protected function resolveAlias($path)
        {
            if($this->application->getConfig()->has('router.aliases'))
            {
                $aliases = Collection::cast($this->application->getConfig()->get('router.aliases'));

                return $aliases->get($path);
            }
        }

        /**
         * @param $path
         *
         * @return callable
         */
        protected function resolveActionClassName($path)
        {

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

            $actionsPathsStack = array_reverse(Collection::cast($this->application->getConfig()->app->actions)->toArray());

            foreach($actionsPathsStack as $nsPrefix => $pathStackEntry)
            {
                if(!is_int($nsPrefix))
                {
                    // only one action path has been set
                    $pathStackEntry = [$nsPrefix => $pathStackEntry];
                }

                foreach($pathStackEntry as $nsPrefix => $path)
                {
                    $fullClassName = $nsPrefix . $className;

                    $fullPath = $path . '/' . str_replace('\\', '/', $className) . '.php';
                    if (file_exists($fullPath) && !class_exists($fullClassName, false))
                    {
                        require_once $fullPath;
                        if (class_exists('\\' . $fullClassName))
                        {
                            return  $fullClassName;
                        }
                    }
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