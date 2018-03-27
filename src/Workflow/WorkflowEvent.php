<?php
/**
 * Created by PhpStorm.
 * User: gde
 * Date: 27/03/2018
 * Time: 10:52
 */

namespace ObjectivePHP\Application\Workflow;


use ObjectivePHP\Application\ApplicationAwareTrait;
use ObjectivePHP\Application\ApplicationInterface;
use ObjectivePHP\Events\Event;

class WorkflowEvent extends Event implements WorkflowEventInterface
{
    use ApplicationAwareTrait;

    const BOOTSTRAP_INIT = 'workflow.bootstrap.init';
    const BOOTSTRAP_DONE = 'workflow.bootstrap.done';
    const ROUTING_DONE = 'workflow.routing.done';
    const REQUEST_HANDLING_DONE = 'workflow.request.handling.done';
    const RESPONSE_SENT = 'workflow.response.sent';
    const PACKAGES_READY = 'workflow.packages.ready';
    const ROUTING_START = 'workflow.routing.start';
    const PACKAGES_INIT = 'workflow.packages.init';
    const REQUEST_HANDLING_START = 'workflow.request.handling.start';

    public function __construct(ApplicationInterface $application)
    {
        $this->setApplication($application);
        parent::__construct();
    }
}