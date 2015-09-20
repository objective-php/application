<?php

    namespace ObjectivePHP\Application\Task\Rta;
    
    
    use ObjectivePHP\Application\ApplicationInterface;
    use ObjectivePHP\Application\Workflow\Event\WorkflowEvent;
    use ObjectivePHP\Primitives\Collection\Collection;
    use ObjectivePHP\Primitives\String\String;

    /**
     * Class ResolveView
     *
     * @package ObjectivePHP\Application\Task\Rta
     */
    class ResolveView
    {
        /**
         * @var ApplicationInterface
         */
        protected $application;

        /**
         * @param WorkflowEvent $event
         *
         * @return $this|mixed|null
         */
        public function __invoke(WorkflowEvent $event)
        {

            $this->setApplication($event->getApplication());

            return $this->getViewName();
        }

        /**
         * @return $this|mixed|null
         */
        public function getViewName()
        {
            // get action
            $action = $this->getApplication()->getWorkflow()->getStep('route')->getEarlierEvent('resolve')
                           ->getResults()['action-resolver'];

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

        /**
         * @param $path
         *
         * @return mixed|null
         * @throws \ObjectivePHP\Primitives\Exception
         */
        protected function resolveAlias($path)
        {
            $config = $this->getApplication()->getConfig();

            return Collection::cast($config->router->aliases)->get($path);
        }


    }