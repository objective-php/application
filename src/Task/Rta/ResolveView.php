<?php

    namespace ObjectivePHP\Application\Task\Rta;
    
    
    use ObjectivePHP\Application\Action\RenderableActionInterface;
    use ObjectivePHP\Application\ApplicationInterface;
    use ObjectivePHP\Application\Workflow\Event\WorkflowEvent;
    use ObjectivePHP\Primitives\Collection\Collection;
    use ObjectivePHP\Primitives\String\String;
    use ObjectivePHP\ServicesFactory\Reference;

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

            return $this->getViewTemplate();
        }

        /**
         * @return $this|mixed|null
         */
        public function getViewTemplate()
        {
            // get action
            $action = $this->getApplication()->getWorkflow()->getStep('route')->getEarlierEvent('resolve')
                           ->getResults()['action-resolver'];


            if($action instanceof Reference)
            {
                $action = $this->getApplication()->getServicesFactory()->get($action->getId());
            }

            if (!$action instanceof RenderableActionInterface)
            {
                return null;
            }

            return $action->getViewTemplate();


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