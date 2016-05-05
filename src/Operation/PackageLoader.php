<?php

    namespace ObjectivePHP\Application\Operation;
    
    
    use ObjectivePHP\Application\ApplicationInterface;

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