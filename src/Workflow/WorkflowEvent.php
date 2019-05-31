<?php
/**
 * Created by PhpStorm.
 * User: gde
 * Date: 27/03/2018
 * Time: 10:52
 */

namespace ObjectivePHP\Application\Workflow;


use ObjectivePHP\Application\ApplicationAccessorsTrait;
use ObjectivePHP\Application\ApplicationInterface;
use ObjectivePHP\Events\Event;

class WorkflowEvent extends Event implements WorkflowEventInterface
{
    use ApplicationAccessorsTrait;

    // common
    const BOOTSTRAP_INIT = 'workflow.bootstrap.init';
    const BOOTSTRAP_DONE = 'workflow.bootstrap.done';
    const PACKAGES_INIT = 'workflow.packages.init';
    const PACKAGES_READY = 'workflow.packages.ready';

    // http
    const ROUTING_START = 'workflow.routing.start';
    const ROUTING_DONE = 'workflow.routing.done';
    const REQUEST_HANDLING_START = 'workflow.request.handling.start';
    const MIDDLEWARE_START = 'workflow.middleware.start';
    const MIDDLEWARE_DONE = 'workflow.middleware.done';
    const REQUEST_HANDLING_DONE = 'workflow.request.handling.done';
    const RESPONSE_READY = 'workflow.response.ready';
    const RESPONSE_SENT = 'workflow.response.sent';

    // cli

    public function __construct(ApplicationInterface $application)
    {
        $this->setApplication($application);
        parent::__construct();
    }
}
