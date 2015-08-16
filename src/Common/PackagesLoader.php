<?php

    namespace ObjectivePHP\Application\Common;
    
    
    use ObjectivePHP\Application\Workflow\Event\WorkflowEvent;

    class PackagesLoader
    {
        public function __invoke(WorkflowEvent $event)
        {

            $application = $event->getApplication();

            $appConfig = $application->getConfig();
            $eventsHandler = $application->getEventsHandler();

            foreach($appConfig->packages->registered as $packageClass)
            {
                $eventsHandler->bind($application->getWorkflow()->getName() . '.packages.load', $packageClass);
            }

        }
    }