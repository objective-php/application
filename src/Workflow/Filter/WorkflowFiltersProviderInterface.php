<?php
/**
 * Created by PhpStorm.
 * User: gde
 * Date: 06/12/2017
 * Time: 16:21
 */

namespace ObjectivePHP\Application\Workflow\Filter;


interface WorkflowFiltersProviderInterface
{

    public function getFilters() : array;

    public function clearFilters();

}