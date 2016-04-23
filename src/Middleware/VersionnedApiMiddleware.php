<?php
/**
 * Created by PhpStorm.
 * User: gauthier
 * Date: 23/04/2016
 * Time: 10:14
 */

namespace ObjectivePHP\Application\Middleware;


use ObjectivePHP\Application\Action\HttpAction;
use ObjectivePHP\Application\ApplicationInterface;

abstract class VersionnedApiMiddleware extends SubRoutingMiddleware
{

    use HttpAction {
        // HttpAction::run as __unused;
    }

    protected $defaultVersion = '1.0';

    protected $versionParameter = 'version';

    public function route()
    {

        $version = $this->getApplication()->getRequest()->getParameters()->get($this->versionParameter) ?: $this->defaultVersion;

        return $version;
    }

    /**
     * Return a list of  
     * 
     * @return array
     */
    public function listAvailableVersions()
    {
        return $this->getMiddlewareStack()->keys()->toArray();
    }


}