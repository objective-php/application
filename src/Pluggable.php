<?php

namespace ObjectivePHP\Application;

/**
 * @deprecated What should we do about it ?
 */
interface Pluggable
{
    /**
     * @param ApplicationInterface $application
     * @param FilterProviderInterface $filters
     * @return ApplicationInterface
     */
    public function plug(ApplicationInterface $application, FilterProviderInterface $filters): ApplicationInterface;

    /**
     * @return string
     */
    public function getReference(): string;
}
