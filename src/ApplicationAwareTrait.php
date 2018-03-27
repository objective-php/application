<?php
/**
 * Created by PhpStorm.
 * User: gde
 * Date: 26/03/2018
 * Time: 17:54
 */

namespace ObjectivePHP\Application;

use ObjectivePHP\ServicesFactory\Annotation\Inject;

trait ApplicationAwareTrait
{
    /**
     * @Inject(service="application")
     * @var ApplicationInterface
     */
    protected $application;

    /**
     * @return ApplicationInterface
     */
    public function getApplication(): ApplicationInterface
    {
        return $this->application;
    }

    /**
     * @param ApplicationInterface $application
     */
    public function setApplication(ApplicationInterface $application)
    {
        $this->application = $application;
    }

}