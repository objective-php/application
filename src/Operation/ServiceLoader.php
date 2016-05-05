<?php

namespace ObjectivePHP\Application\Operation;


use ObjectivePHP\Application\ApplicationInterface;
use ObjectivePHP\Invokable\AbstractInvokable;
use ObjectivePHP\ServicesFactory\Config\Service;

/**
 * Class ServiceLoader
 * @package ObjectivePHP\Application\Operation\Common
 */
class ServiceLoader extends AbstractInvokable
{
    /**
     * @param ApplicationInterface $app
     *
     * @throws \ObjectivePHP\ServicesFactory\Exception\Exception
     */
    public function run(ApplicationInterface $app)
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
