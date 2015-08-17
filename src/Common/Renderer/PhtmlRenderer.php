<?php

    namespace ObjectivePHP\Application\Common\Renderer;

    use ObjectivePHP\Application\ApplicationInterface;
    use ObjectivePHP\Application\Exception;
    use ObjectivePHP\Application\Workflow\Event\WorkflowEvent;
    use ObjectivePHP\Primitives\Collection\Collection;
    use ObjectivePHP\Primitives\String\String;

    class PhtmlRenderer
    {

        /**
         * @var ApplicationInterface
         */
        protected $application;

        public function __invoke(WorkflowEvent $event)
        {

            $this->application = $application = $event->getApplication();

            // get action
            $action = $application->getWorkflow()->getStep('run')->getEarlierEvent('route')->getResults()[0];



            if(is_object($action))
            {
                // look for getViewPath() method
                // TODO create ActionInterface to define such methods

                if(method_exists($action, 'getViewName') && $viewName = $action->getViewName())
                {
                    goto render;
                }
            }

            render:

            if(!isset($viewName))
            {
                $viewName = $path = $event->getApplication()->getRequest()->getUri()->getPath();
                if ($alias = $this->resolveAlias($path))
                {
                    $viewName = $alias;
                }

                $viewName = String::cast($viewName)->trim('/');

            }

            $context = $application->getWorkflow()->getStep('run')->getEarlierEvent('execute')->getResults()[0];

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
            $viewLocations = array_reverse($this->application->getConfig()->get('app.views.locations'));


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
    }