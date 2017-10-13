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
        $getVersion = $request->getParameters()->get($this->getVersionParameter());
        $headVersion = $request->getHeader($this->getVersionHeader());
        $header = $headVersion[0] ?? null;
        $version = $getVersion ?: $header;

        if (empty($version)) {
            $version = $this->getDefaultVersion();
        }

        if (!empty($version) && !in_array($version, $this->listAvailableVersions())) {
            throw new Exception("Version not found", 404);
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
}
