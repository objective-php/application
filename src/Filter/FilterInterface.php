<?php
/**
 * Created by PhpStorm.
 * User: gauthier
 * Date: 27/02/2018
 * Time: 15:54
 */

namespace ObjectivePHP\Application\Filter;


use ObjectivePHP\Application\ApplicationInterface;

interface FilterInterface
{
    public function filter(ApplicationInterface $app) : bool;
}
