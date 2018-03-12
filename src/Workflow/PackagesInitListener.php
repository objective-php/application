<?php
/**
 * Created by PhpStorm.
 * User: gde
 * Date: 12/03/2018
 * Time: 19:28
 */

namespace ObjectivePHP\Application\Workflow;


use ObjectivePHP\Events\EventInterface;

interface PackagesInitListener
{

    public function onPackagesInit(EventInterface $event);

}