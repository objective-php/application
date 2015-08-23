<?php

    namespace ObjectivePHP\Application\Task\Common;
    
    
    use ObjectivePHP\Application\Workflow\Event\WorkflowEvent;

    class BootstrapPackages
    {
        public function __invoke(WorkflowEvent $event)
        {

            $application = $event->getApplication();

            $appConfig = $application->getConfig();

            foreach($appConfig->packages->registered as $packageClass)
            {
                $application->getWorkflow()->bind('packages.load', $packageClass);
            }
        }
    }