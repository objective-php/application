<?php
/**
 * Created by PhpStorm.
 * User: gde
 * Date: 27/03/2018
 * Time: 10:54
 */

namespace ObjectivePHP\Application\Workflow;


use ObjectivePHP\Application\ApplicationAwareInterface;
use ObjectivePHP\Application\ApplicationInterface;
use ObjectivePHP\Events\EventInterface;

interface WorkflowEventInterface extends EventInterface, ApplicationAwareInterface
{

    public function getApplication(): ApplicationInterface;

}