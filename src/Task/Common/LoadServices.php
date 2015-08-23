<?php

    namespace ObjectivePHP\Application\Task\Common;
    
    
    use ObjectivePHP\Application\ApplicationInterface;
    use ObjectivePHP\Application\Workflow\Event\WorkflowEvent;
    use ObjectivePHP\ServicesFactory\ServicesFactory;

    class LoadServices
    {
        public function __invoke(WorkflowEvent $event)
        {
            $config = $event->getApplication()->getConfig();

            $servicesFactory = $event->getApplication()->getServicesFactory();

            if($event->getName()->matches('/.*\.bootstrap/'))
            {
                $this->injectInitialServices($servicesFactory, $event->getApplication());
            }

            foreach($config->services as $serviceSpec)
            {
                $servicesFactory->registerService($serviceSpec);
            }
        }

        /**
         * @param ServicesFactory      $servicesFactory
         * @param ApplicationInterface $application
         *
         * @throws \ObjectivePHP\ServicesFactory\Exception
         */
        protected function injectInitialServices(ServicesFactory $servicesFactory, ApplicationInterface $application)
        {
            $servicesFactory->registerService(
                ['id' => 'application', 'instance' => $application],

                // all those are here for convenience only since 'application' gives access to all of them
                ['id' => 'config', 'instance' => $application->getConfig()],
                ['id' => 'events-handler', 'instance' => $application->getEventsHandler()],
                ['id' => 'workflow', 'instance' => $application->getWorkflow()]
            );
        }
    }