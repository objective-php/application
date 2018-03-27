<?php

namespace ObjectivePHP\Application;

use ObjectivePHP\ServicesFactory\Specs\InjectionAnnotationProvider;

/**
 * Interface ApplicationAwareInterface
 *
 * @package ObjectivePHP\Application
 */
interface ApplicationAwareInterface extends InjectionAnnotationProvider
{
    /**
     * @param ApplicationInterface $application
     */
    public function setApplication(ApplicationInterface $application);
}
