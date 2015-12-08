<?php

    namespace ObjectivePHP\Application\Action;
    
    
    use ObjectivePHP\Application\ApplicationInterface;
    use ObjectivePHP\Application\Workflow\Event\WorkflowEvent;

    /**
     * Interface ActionInterface
     *
     * @package ObjectivePHP\Application\Action
     */
    interface ActionInterface
    {
        /**
         * @param WorkflowEvent $event
         *
         * @return mixed
         */
        public function __invoke(ApplicationInterface $application);
    }