<?php

    namespace ObjectivePHP\Application\Workflow\Filter;

    use ObjectivePHP\Application\ApplicationInterface;
    use ObjectivePHP\Application\Exception;

    /**
     * Class RouteFilter
     *
     * @package ObjectivePHP\Application\Workflow
     */
    class UrlFilter extends AbstractFilter
    {
        /**
         * @return bool
         */
        public function filter(ApplicationInterface $app) : bool
        {
            // check route filter
            if ($this->getFilter() != '*')
            {

                $request = $app->getRequest();

                if (!$request)
                {
                    throw new Exception('Cannot run UrlFilter: no request has been set');
                }

                // use route as reference to match route filter, but default to URL if
                // no route has been set yet
                $path = $request->getUri()->getPath();

                if (!$app->getRouteMatcher()->match($this->getFilter(), $path))
                {
                    return false;
                }
            }

            return true;
        }

    }