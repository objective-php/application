<?php

namespace ObjectivePHP\Application\Action;

abstract class VersionedApiAction extends SubRoutingAction
{

    protected $defaultVersion = '1.0';

    protected $versionParameter = 'version';

    protected $versionHeader = 'API-VERSION';

    public function route()
    {
        $request = $this->getApplication()->getRequest();
        $versionFromGet = $request->getParameters()->get($this->getVersionParameter());
        $versionHeader = $request->getHeader($this->getVersionHeader());
        $versionFromHeader = $versionHeader[0] ?? null;
        $version = $versionFromGet ?: $versionFromHeader ?: $this->getDefaultVersion();

        if (!empty($version) && !in_array($version, $this->listAvailableVersions())) {
            throw new Exception("No API matching requested version is registered", 404);
        }

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

    /**
     * Get DefaultVersion
     *
     * @return string
     */
    public function getDefaultVersion()
    {
        return $this->defaultVersion;
    }

    /**
     * Set DefaultVersion
     *
     * @param string $defaultVersion
     *
     * @return $this
     */
    public function setDefaultVersion($defaultVersion)
    {
        $this->defaultVersion = $defaultVersion;
        return $this;
    }

    /**
     * Get VersionParameter
     *
     * @return string
     */
    public function getVersionParameter()
    {
        return $this->versionParameter;
    }

    /**
     * Set VersionParameter
     *
     * @param string $versionParameter
     *
     * @return $this
     */
    public function setVersionParameter($versionParameter)
    {
        $this->versionParameter = $versionParameter;
        return $this;
    }

    /**
     * Get VersionHeader
     *
     * @return mixed
     */
    public function getVersionHeader()
    {
        return $this->versionHeader;
    }

    /**
     * Set VersionHeader
     *
     * @param mixed $versionHeader
     *
     * @return $this
     */
    public function setVersionHeader($versionHeader)
    {
        $this->versionHeader = $versionHeader;
        return $this;
    }

    /**
     * @param $version
     */
    public function getAppropriateVersion($version)
    {
    }
}
