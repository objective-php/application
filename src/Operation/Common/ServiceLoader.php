<?php

    namespace ObjectivePHP\Application\Operation\Common;
    
    
    use ObjectivePHP\Application\ApplicationInterface;
    use ObjectivePHP\Application\Workflow\Event\WorkflowEvent;
    use ObjectivePHP\Package\Devtools\Package\Debug\Dumper;
    use ObjectivePHP\ServicesFactory\Config\Service;
    use ObjectivePHP\ServicesFactory\ServicesFactory;

    class ServiceLoader
    {
        /**
         * @param ApplicationInterface $app
         *
         * @throws \ObjectivePHP\ServicesFactory\Exception\Exception
         */
        public function __invoke(ApplicationInterface $app)
        {
            $config = $app->getConfig();

            $servicesFactory = $app->getServicesFactory();

            $this->injectInitialServices($app);

            foreach($config->get(Service::class, []) as $serviceSpec)
            {
                $servicesFactory->registerService($serviceSpec);
            }
        }

        /**
         * @param ApplicationInterface $app
         *
         * @throws \ObjectivePHP\ServicesFactory\Exception\Exception
         * @internal param ApplicationInterface $application
         *
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
