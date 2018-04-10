<?php
/**
 * Created by PhpStorm.
 * User: gde
 * Date: 10/04/2018
 * Time: 16:39
 */
namespace ObjectivePHP\Application\Injector;

use ObjectivePHP\Application\ApplicationAwareInterface;
use ObjectivePHP\Config\ConfigAwareInterface;
use ObjectivePHP\ServicesFactory\Injector\InjectorInterface;
use ObjectivePHP\ServicesFactory\ServicesFactory;
use ObjectivePHP\ServicesFactory\Specification\ServiceSpecificationInterface;

class DefaultInjector implements InjectorInterface
{
    public function injectDependencies($instance, ServicesFactory $servicesFactory, ServiceSpecificationInterface $serviceSpecification = null)
    {
        if($instance instanceof ApplicationAwareInterface) {
            $instance->setApplication($servicesFactory->get('application'));
        }

        if($instance instanceof ConfigAwareInterface) {
            $instance->setConfig($servicesFactory->getConfig());
        }
    }


}