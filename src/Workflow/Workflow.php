<?php
/**
 * Created by PhpStorm.
 * User: gauthier
 * Date: 27/02/2018
 * Time: 15:40
 */

namespace ObjectivePHP\Application\Workflow;


abstract class Workflow
{
    const BOOTSTRAP_INIT = 'workflow.bootstrap.init';

    const BOOTSTRAP_DONE = 'workflow.bootstrap.done';

    const PACKAGES_INIT = 'workflow.packages.init';

    const PACKAGES_READY = 'workflow.packages.ready';

    const ROUTING_START = 'workflow.routing.start';

    const ROUTING_DONE = 'workflow.routing.done';

    const REQUEST_HANDLING_START = 'workflow.request.handling.start';

    const REQUEST_HANDLING_DONE = 'workflow.request.handling.done';

    const RESPONSE_SENT = 'workflow.response.sent';

}
