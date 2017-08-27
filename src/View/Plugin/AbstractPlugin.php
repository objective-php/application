<?php
namespace ObjectivePHP\Application\View\Plugin;

use ObjectivePHP\Application\ApplicationInterface;

abstract class AbstractPlugin implements PluginInterface
{
    /** @var ApplicationInterface $application */
    protected $application;

    /** @inheritdoc */
    public function getApplication(): ApplicationInterface
    {
        return $this->application;
    }

    /** @inheritdoc */
    public function setApplication(ApplicationInterface $application): PluginInterface
    {
        $this->application = $application;

        return $this;
    }
}