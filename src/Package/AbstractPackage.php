<?php


namespace ObjectivePHP\Application\Package;


use ObjectivePHP\Application\Workflow\PackagesInitListener;
use ObjectivePHP\Application\Workflow\PackagesReadyListener;
use ObjectivePHP\Application\Workflow\WorkflowEventInterface;
use ObjectivePHP\Config\ConfigInterface;
use ObjectivePHP\Config\ConfigProviderInterface;
use ObjectivePHP\Config\DirectivesProviderInterface;
use ObjectivePHP\Config\Loader\FileLoader\FileLoader;
use ObjectivePHP\Config\ParametersProviderInterface;

class AbstractPackage implements PackageInterface
{
    public function getDirectives(): array
    {
        // should be overridden in package if needed
        return [];
    }

    public function getParameters(): array
    {
        $params = [];

        $reflected = new \ReflectionObject($this);
        $location = dirname($reflected->getFileName());

        // workaround for packages stored in the src/ folder
        // instead of the root directory of their repository
        if(basename($location) == 'src') {
            $location .= '/..';
        }

        $location .= '/config';


        if(is_dir($location)) {
            $params = (new FileLoader())->load($location);
        } else {
            // TODO collect this
        }

        return $params;
    }

    public function onPackagesInit(WorkflowEventInterface $event)
    {
        // should be overridden in package if needed
    }

    public function onPackagesReady(WorkflowEventInterface $event)
    {
        // should be overridden in package if needed
    }


}