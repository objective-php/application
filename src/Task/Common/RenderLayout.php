<?php

    namespace ObjectivePHP\Application\Task\Common;


    use ObjectivePHP\Application\Workflow\Event\WorkflowEvent;

    /**
     * Class RenderLayout
     *
     * @package ObjectivePHP\Application\Task\Common
     */
    class RenderLayout extends RenderView
    {

        protected $locationDirective = 'layouts.locations';

        /**
         * @param WorkflowEvent $event
         *
         * @return mixed|\ObjectivePHP\Config\Config
         */
        protected function getViewName(WorkflowEvent $event)
        {
            $layout = $this->getApplication()->getConfig()->get('layouts.layout', $this->getApplication()->getConfig()->get('layouts.default', 'layout'));

            return $layout;
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


        /**
         * @param WorkflowEvent $event
         *
         * @return mixed
         * @throws \ObjectivePHP\Events\Exception
         */
        public function getContext(WorkflowEvent $event)
        {

            // insert view
            $event->getApplication()->getResponse()->getBody()->rewind();
            $context['content'] = $event->getApplication()->getResponse()->getBody()->getContents();

            // inject config
            $context['config'] = $this->getApplication()->getConfig();

            $viewContext = $this->getApplication()->getWorkflow()->getStep('run')->getEarlierEvent('execute')->getResults()['action'];


            if(isset($viewContext['layout']))
            {
                foreach($viewContext['layout'] as $var => $value)
                {
                    $context[$var] = $value;
                }
            }

            return $context;
        }

    }