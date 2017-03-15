<?php
/**
 * This file is part of the Objective PHP project
 *
 * More info about Objective PHP on www.objective-php.org
 *
 * @license http://opensource.org/licenses/GPL-3.0 GNU GPL License 3.0
 */

namespace ObjectivePHP\Application\Action\Parameter\Cli;


interface ParameterInterface
{
    
    const MULTIPLE = 1;
    const MANDATORY = 2;
    const SWITCH = 4;
    
    public function getDescription() : string;
    
    public function getShortName() : string;
    
    public function getLongName() : string;
    
    public function hydrate(string $cli) : string;
    
    public function getValue();
    
    public function getOptions() : int;
    
}
