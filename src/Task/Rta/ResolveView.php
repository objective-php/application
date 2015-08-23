<?php

    namespace ObjectivePHP\Application\Task\Rta;
    
    
    use ObjectivePHP\Application\ApplicationInterface;
    use ObjectivePHP\Application\Workflow\Event\WorkflowEvent;
    use ObjectivePHP\Primitives\Collection\Collection;
    use ObjectivePHP\Primitives\String\String;

    class ResolveView
    {
        /**
         * @var ApplicationInterface
         */
        protected $application;

        public function __invoke(WorkflowEvent $event)
        {

            $this->setApplication($event->getApplication());

            return $this->getViewName();
        }

        protected function resolveAlias($path)
        {
            $config = $this->getApplication()->getConfig();

            return Collection::cast($config->router->aliases)->get($path);
        }

        public function getViewName()
        {
            // get action
            $action = $this->getApplication()->getWorkflow()->getStep('route')->getEarlierEvent('resolve')->getResults()['action-resolver'];

            if (is_object($action))
            {
                if (method_exists($action, 'getViewName') && $viewName = $action->getViewName())
                {
                    return $viewName;
                }
            }

            $viewName = $path = $this->getApplication()->getRequest()->getUri()->getPath();
            if ($alias = $this->resolveAlias($path))
            {
                $viewName = $alias;
            }

            return String::cast($viewName)->trim('/');
        }

        /**
         * @return ApplicationInterface
         */
        public function getApplication()
        {
            return $this->application;
        }

        /**
         * @param ApplicationInterface $application
         *
         * @return $this
         */
        public function setApplication($application)
        {
            $this->application = $application;

            return $this;
        }


    }