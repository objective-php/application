<?php

    namespace ObjectivePHP\Application\Operation\Common;
    
    
    use ObjectivePHP\Application\ApplicationInterface;
    use ObjectivePHP\Application\Workflow\Event\WorkflowEvent;
    use ObjectivePHP\Package\Devtools\Package\Debug\Dumper;
    use ObjectivePHP\ServicesFactory\ServicesFactory;

    class ServiceLoader
    {
        public function __invoke(ApplicationInterface $app)
        {
            $config = $app->getConfig();

            $servicesFactory = $app->getServicesFactory();

            $this->injectInitialServices($app);

            foreach($config->get('services', []) as $serviceSpec)
            {
                $servicesFactory->registerService($serviceSpec);
            }
        }

        /**
         * @param ApplicationInterface $application
         *
         * @throws \ObjectivePHP\ServicesFactory\Exception
         */
        protected function injectInitialServices(ApplicationInterface $app)
        {
            $app->getServicesFactory()->registerService(
                ['id' => 'application', 'instance' => $app],

                // all those are here for convenience only since 'application' gives access to all of them
                ['id' => 'config', 'instance' => $app->getConfig()],
                ['id' => 'events-handler', 'instance' => $app->getEventsHandler()]
            );
        }
    }
