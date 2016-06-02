<?php

namespace ObjectivePHP\Application\Action;

abstract class VersionedApiAction extends SubRoutingAction
{

    protected $defaultVersion = '1.0';

    protected $versionParameter = 'version';

    // TODO allow specifying version using Header 
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