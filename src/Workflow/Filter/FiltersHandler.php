<?php
/**
 * This file is part of the Objective PHP project
 *
 * More info about Objective PHP on www.objective-php.org
 *
 * @license http://opensource.org/licenses/GPL-3.0 GNU GPL License 3.0
 */

namespace ObjectivePHP\Application\Workflow\Filter;


use ObjectivePHP\Application\ApplicationInterface;
use ObjectivePHP\Invokable\Invokable;
use ObjectivePHP\Invokable\InvokableInterface;
use ObjectivePHP\Primitives\Collection\Collection;

trait FiltersHandler
{
    
    /**
     * @var Collection
     */
    protected $filters;
    
    /**
     * @param $filter
     */
    public function addFilter($filter)
    {
        if (is_null($this->filters))
        {
            $this->initFiltersCollection();
            
        }
        $this->filters->append(Invokable::cast($filter));
    }
    
    /**
     * @return $this
     */
    protected function initFiltersCollection()
    {
        $this->filters = (new Collection())->restrictTo(InvokableInterface::class);
        
        return $this;
    }
    
    /**
     * @param ApplicationInterface $app
     *
     * @return bool
     */
    public function runFilters(ApplicationInterface $app)
    {
        
        if(is_null($this->filters))
        {
            // no filter has been set
            return true;
        }
        
        /**
         * @var callable $filter
         */
        foreach ($this->getFilters() as $filter)
        {
            if ($filter instanceof InvokableInterface)
            {
                $filter->setApplication($app);
            }
            
            if (!$filter($app))
            {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * @return Collection
     */
    public function getFilters()
    {
        return $this->filters;
    }
    
    /**
     * @param array $filters
     *
     * @return $this
     */
    public function setFilters($filters)
    {
        
        Collection::cast($filters)->each(function ($filter)
        {
            $this->addFilter($filter);
        })
        ;
        
        return $this;
    }
}
