<?php

    namespace ObjectivePHP\Application\Task\Common;

    use ObjectivePHP\Application\ApplicationInterface;
    use ObjectivePHP\Application\Exception;
    use ObjectivePHP\Application\View\Helper\Vars;
    use ObjectivePHP\Application\Workflow\Event\WorkflowEvent;
    use ObjectivePHP\Primitives\Collection\Collection;

    class RenderView
    {

        protected $locationDirective = 'views.locations';

        /**
         * @var ApplicationInterface
         */
        protected $application;

        public function __invoke(WorkflowEvent $event)
        {

            $this->setApplication($application = $event->getApplication());

            $viewName = $this->getViewName($event);

            $context = $this->getContext($event);

            return $this->render($viewName, $context);


        }

        protected function getViewName(WorkflowEvent $event)
        {
            return $event->getResults()['view-resolver'];
        }

        protected function getContext(WorkflowEvent $event)
        {

            $viewVars = $this->getApplication()->getWorkflow()->getStep('run')->getEarlierEvent('execute')->getResults()['action'];

            // inject config
            $context['config'] = $this->getApplication()->getConfig();

            foreach($viewVars as $reference => $value)
            {
                Vars::set($reference, $value);
            }

            return $context;
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

        public function render($viewName, $context = [])
        {
            $viewPath = $this->resolveViewPath($viewName);

            // make view and layout path available to the rest of the application
            if($this instanceof RenderLayout)
            {
                Vars::set('layout.path', $viewPath);
            }
            else
            {
                Vars::set('view.path', $viewPath);
            }

            if (is_null($viewPath))
            {
                throw new Exception(sprintf('Unable to resolve view "%s" to a file path (views locations: %s)', $viewName, implode(', ', $this->getViewsLocations())));
            }

            if ($context)
            {
                extract($context);
                unset($context);
            }

            ob_start();
            include $viewPath;
            $view = ob_get_clean();

            return $view;
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
            $config = $this->getApplication()->getConfig();

            if ($config->hasDirective($this->locationDirective))
            {
                $viewLocations = array_reverse(Collection::cast($config->get($this->locationDirective))->toArray());
            }
            else return [];

            $locations = [];
            foreach ($viewLocations as $paths)
            {
                $locations[] = $paths;
            }

            return $locations;

        }

    }