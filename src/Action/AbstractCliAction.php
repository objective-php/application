<?php
/**
 * This file is part of the Objective PHP project
 *
 * More info about Objective PHP on www.objective-php.org
 *
 * @license http://opensource.org/licenses/GPL-3.0 GNU GPL License 3.0
 */

namespace ObjectivePHP\Application\Action;


use ObjectivePHP\Application\ApplicationInterface;

abstract class AbstractCliAction implements CliActionInterface
{
    public function  __invoke(ApplicationInterface $app) {
        return $this->run($app);
    }
    
    abstract public function run(ApplicationInterface $app);
}
