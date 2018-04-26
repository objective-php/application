<?php

namespace ObjectivePHP\Application\Injector;

use ObjectivePHP\Application\ApplicationAwareInterface;
use ObjectivePHP\Config\ConfigAwareInterface;
use ObjectivePHP\ServicesFactory\Injector\InjectorInterface;
use ObjectivePHP\ServicesFactory\ServicesFactory;
use ObjectivePHP\ServicesFactory\Specification\ServiceSpecificationInterface;

/**
 * Class DefaultInjector
 *
 * @package ObjectivePHP\Application\Injector
 */
class DefaultInjector implements InjectorInterface
{
    public function injectDependencies(
        $instance,
        ServicesFactory $servicesFactory,
        ServiceSpecificationInterface $serviceSpecification = null
    ) {
        if ($instance instanceof ApplicationAwareInterface) {
            $instance->setApplication($servicesFactory->get('application'));
        }

        if ($instance instanceof ConfigAwareInterface) {
            $instance->setConfig($servicesFactory->getConfig());
        }
    }
}
