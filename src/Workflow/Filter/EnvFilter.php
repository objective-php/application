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
    public function run(ApplicationInterface $app): bool
    {
        $validEnvironments = (array) $this->getFilter();
        $env = $app->getEnv();

        $result = false;
        foreach ($validEnvironments as $validEnvironment) {
            if (strpos($validEnvironment, '!') === 0) {
                if ($env == substr($validEnvironment, 1)) return false;
                else $result = true;
            } else {
                if ($env == $validEnvironment) return true;
            }
        }

        return $result;
    }
}