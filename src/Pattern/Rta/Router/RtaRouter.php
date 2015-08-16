<?php
    namespace ObjectivePHP\Application\Pattern\Rta\Router;


    use ObjectivePHP\Application\ApplicationInterface;
    use ObjectivePHP\Application\Workflow\Event\WorkflowEvent;
    use ObjectivePHP\Primitives\Collection\Collection;
    use ObjectivePHP\Primitives\String\String;

    class RtaRouter
    {
        public function __invoke(WorkflowEvent $event)
        {

            $application = $event->getApplication();

            $path = $event->getApplication()->getRequest()->getUri()->getPath();


            if($alias = $this->resolveAlias($application, $path))
            {
                $path = $alias;
            }

            $action = $this->computeClassFullyQualifiedName($path);

            $application->getWorkflow()->bind('run.execute', new $action);

        }

        protected function resolveAlias(ApplicationInterface $application, $path)
        {
            if($application->getConfig()->has('router.aliases'))
            {
                $aliases = Collection::cast($application->getConfig()->get('router.aliases'));

                return $aliases->get($path);
            }
        }

        protected function computeClassFullyQualifiedName($path)
        {
            // prefix path with 'action' to force Action namespace

            $path = String::cast($path);

            $path->prepend('poc/action/')->trim('/');

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

            return  str_replace('\\\\', '\\', implode($backslash, $namespaces->toArray()));



        }
    }