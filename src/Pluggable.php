<?php

namespace ObjectivePHP\Application;

/**
 * Interface Pluggable
 * @package ObjectivePHP\Application
 */
interface Pluggable
{
    /**
     * @param Application $application
     * @return Application
     */
    public function plug(Application $application): Application;

    /**
     * @return string
     */
    public function getReference(): string;
}
