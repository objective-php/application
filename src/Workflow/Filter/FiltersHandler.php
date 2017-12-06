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
use ObjectivePHP\Application\Workflow\Hook;
use ObjectivePHP\Invokable\Invokable;
use ObjectivePHP\Invokable\InvokableInterface;
use ObjectivePHP\Primitives\Collection\Collection;
use ObjectivePHP\ServicesFactory\Exception\Exception as ServicesFactoryException;
use ObjectivePHP\ServicesFactory\ServiceReference;

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
        $filters = $this->getFilters() ?? [];
        if($this instanceof Hook) {
            $middleware = $this->getMiddleware();

            if ($middleware instanceof WorkflowFiltersProviderInterface) {
                $filters = array_merge($filters, $middleware->getFilters());
            }
        }

        if(!$filters)
        {
            // no filter has been set
            return true;
        }
        
        /**
         * @var Invokable $filter
         */
        foreach ($filters as $filter)
        {
            $filter->setApplication($app);
            
            if($filter instanceof ServiceReference)
            {
                try {
                    $filter = $app->getServicesFactory()->get($filter);
                } catch(ServicesFactoryException $e)
                {
                    throw new FilterException(sprintf('The service "%s" set as workflow filter has not been found.', $filter->getId()), null, $e);
                }
            } else {
                $callable = $filter->getCallable();
                // if the filter is not a service, let's pass it to the injection loop anyway
                if(is_object($callable)) {
                    $app->getServicesFactory()->injectDependencies($callable);
                }
                $filter = $callable;
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
