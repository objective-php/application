<?php

namespace ObjectivePHP\Application;

use ObjectivePHP\ServicesFactory\Specification\InjectionAnnotationProvider;

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
