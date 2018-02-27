<?php
/**
 * Created by PhpStorm.
 * User: gde
 * Date: 06/12/2017
 * Time: 16:21
 */

namespace ObjectivePHP\Application\Filter;


use ObjectivePHP\Application\ApplicationInterface;
use ObjectivePHP\Primitives\Collection\Collection;

interface FiltersProviderInterface
{

    public function getFilters() : Collection;

    public function clearFilters();
    
    public function runFilters(ApplicationInterface $app);

}
