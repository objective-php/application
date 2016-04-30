<?php
/**
 * Created by PhpStorm.
 * User: gauthier
 * Date: 30/04/2016
 * Time: 19:36
 */

namespace ObjectivePHP\Application\Workflow\Filter;


use ObjectivePHP\Application\ApplicationInterface;

class EnvFilter extends AbstractFilter
{
    /**
     * @param ApplicationInterface $app
     * @return bool
     */
    public function run(ApplicationInterface $app) : bool
    {
        $env = (array) $this->getFilter();

        return in_array($app->getEnv(), $env);
    }
}