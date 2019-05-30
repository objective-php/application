<?php

namespace ObjectivePHP\Application\Package;


use ObjectivePHP\Application\Workflow\PackagesInitListener;
use ObjectivePHP\Application\Workflow\PackagesReadyListener;
use ObjectivePHP\Config\DirectivesProviderInterface;
use ObjectivePHP\Config\ParametersProviderInterface;

interface PackageInterface extends PackagesInitListener, PackagesReadyListener, DirectivesProviderInterface, ParametersProviderInterface
{

}
