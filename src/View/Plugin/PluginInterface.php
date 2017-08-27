<?php
namespace ObjectivePHP\Application\View\Plugin;

use ObjectivePHP\Application\ApplicationInterface;

/**
 * Interface PluginInterface
 *
 * @package ObjectivePHP\Application\View\Plugin
 */
interface PluginInterface
{
    /**
     * @param array ...$args
     * @return mixed
     */
    public function __invoke(...$args);

    /**
     * Get Application
     *
     * @return ApplicationInterface
     */
    public function getApplication(): ApplicationInterface;

    /**
     * Set Application
     *
     * @param ApplicationInterface $application
     *
     * @return PluginInterface
     */
    public function setApplication(ApplicationInterface $application): PluginInterface;
}