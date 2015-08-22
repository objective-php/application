<?php

    namespace ObjectivePHP\Application\Common\Renderer;

    use ObjectivePHP\Application\ApplicationInterface;
    use ObjectivePHP\Application\Exception;
    use ObjectivePHP\Application\Workflow\Event\WorkflowEvent;
    use ObjectivePHP\Primitives\Collection\Collection;
    use ObjectivePHP\Primitives\String\String;

    class ViewRenderer
    {

        /**
         * @var ApplicationInterface
         */
        protected $application;

        public function __invoke(WorkflowEvent $event)
        {

            $this->setApplication($application = $event->getApplication());

            $viewName = $this->getViewName();

            $context = $this->getContext();

            return $this->renderView($viewName, $context);


        }

        public function renderView($viewName, $context = [])
        {
            $viewPath = $this->resolveViewPath($viewName);

            if (is_null($viewPath))
            {
                throw new Exception(sprintf('Unable to resolve view "%s" to a file path (views locations: %s)' , $viewName, implode(', ', $this->getViewsLocations())));
            }

            if($context)
            {
                extract($context);
                unset($context);
            }

            include $viewPath;

            return $viewPath;
        }

        /**
         * @param $viewName
         *
         * @return callable
         */
        protected function resolveViewPath($viewName)
        {

            $viewLocations = $this->getViewsLocations();

            foreach ($viewLocations as $location)
            {
                $fullPath = $location . '/' . $viewName . '.phtml';
                if (file_exists($fullPath))
                {
                    return $fullPath;
                }
            }

            return null;
        }

        protected function getViewsLocations()
        {
            $viewLocations = array_reverse($this->application->getConfig()->get('views.locations'));

            $locations = [];
            foreach ($viewLocations as $paths)
            {
                if (!is_array($paths))
                {
                    // only one action path has been set
                    $paths = [$paths];
                }

                $locations += $paths;

            }

            return $locations;
        }

        protected function resolveAlias($path)
        {
            if ($this->application->getConfig()->has('router.aliases'))
            {
                $aliases = Collection::cast($this->application->getConfig()->get('router.aliases'));

                return $aliases->get($path);
            }
        }

        protected function getContext()
        {
            $context = $this->getApplication()->getWorkflow()->getStep('run')->getEarlierEvent('execute')->getResults()[0];

            // unset context specific variables
            unset($context['layout']);

            return $context;
        }

        public function getViewName()
        {
            // get action
            $action = $this->getApplication()->getWorkflow()->getStep('run')->getEarlierEvent('route')->getResults()[0];

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