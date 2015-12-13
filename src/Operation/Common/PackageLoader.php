<?php

    namespace ObjectivePHP\Application\Operation\Common;
    
    
    use ObjectivePHP\Application\ApplicationInterface;
    use ObjectivePHP\Application\Workflow\Event\WorkflowEvent;

    class PackageLoader
    {
        public function __invoke(ApplicationInterface $application)
        {

            $appConfig = $application->getConfig();

            foreach($appConfig->packages->registered as $packageClass)
            {
                $application->getStep('init')->plug($packageClass);
            }
        }
    }