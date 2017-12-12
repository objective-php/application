<?php

namespace ObjectivePHP\Application;

use ObjectivePHP\Application\Bridge\Psr\RequestHandlerInterface;

/**
 * Interface HttpApplication
 * @package ObjectivePHP\Application
 */
interface HttpApplication extends Application, RequestHandlerInterface
{

}
