<?php
/**
 * This file is part of the Objective PHP project
 *
 * More info about Objective PHP on www.objective-php.org
 *
 * @license http://opensource.org/licenses/GPL-3.0 GNU GPL License 3.0
 */

namespace ObjectivePHP\Application\Filter;


use ObjectivePHP\Application\ApplicationInterface;
use ObjectivePHP\Primitives\Collection\Collection;

trait FiltersProviderTrait
{

    /**
     * @var Collection
     */
    protected $filters;

    public function clearFilters()
    {
        $this->initFiltersCollection();
    }

    /**
     * @return $this
     */
    protected function initFiltersCollection()
    {
        $this->filters = (new Collection())->restrictTo(FilterInterface::class);

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

        if (!$filters) {
            // no filter has been set
            return true;
        }

        /**
         * @var FilterInterface $filter
         */
        foreach ($filters as $filter) {

            // TODO check that current class actually implements ServicexsFactoryAwareInterface
            $app->getServicesFactory()->injectDependencies($filter);

            if (!$filter->filter($app)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return Collection
     */
    public function getFilters(): Collection
    {
        return $this->filters;
    }

    /**
     * @param array $filters
     *
     * @return $this
     */
    public function registerFilters($filters)
    {

        Collection::cast($filters)->each(function ($filter) {
            $this->registerFilter($filter);
        });

        return $this;
    }

    /**
     * @param $filter
     */
    public function registerFilter(FilterInterface $filter)
    {
        if (is_null($this->filters)) {
            $this->initFiltersCollection();

        }

        $this->filters->append($filter);
    }
}
