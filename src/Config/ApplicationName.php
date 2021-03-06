<?php
/**
 * This file is part of the Objective PHP project
 *
 * More info about Objective PHP on www.objective-php.org
 *
 * @license http://opensource.org/licenses/GPL-3.0 GNU GPL License 3.0
 */

namespace ObjectivePHP\Application\Config;


use ObjectivePHP\Config\Directive\AbstractScalarDirective;

class ApplicationName extends AbstractScalarDirective
{
    const KEY = 'application.name';
    protected $key = self::KEY;
}
