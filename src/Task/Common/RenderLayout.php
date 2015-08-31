<?php

    namespace ObjectivePHP\Application\Task\Common;


    use ObjectivePHP\Application\Workflow\Event\WorkflowEvent;

    class RenderLayout extends RenderView
    {

        protected $locationDirective = 'layouts.locations';

        protected function getViewName(WorkflowEvent $event)
        {
            $layout = $this->getApplication()->getConfig()->get('layouts.layout', $this->getApplication()->getConfig()->get('layouts.default', 'layout'));

            return $layout;
        }

        public function getContext(WorkflowEvent $event)
        {

            // insert view
            $context['content'] = $event->getResults()['view-renderer'];

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