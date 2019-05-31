<?php
    /**
     * Created by PhpStorm.
     * User: gauthier
     * Date: 08/12/2015
     * Time: 12:16
     */

    namespace ObjectivePHP\Application\Exception\Filter;

    use ObjectivePHP\Application\ApplicationInterface;
    use ObjectivePHP\Application\Exception\ApplicationException;

    /**
     * Class RouteFilter
     *
     * @package ObjectivePHP\Application\Workflow
     */
    class RouteFilter extends AbstractWorkflowFilter
    {

        /**
         * @param ApplicationInterface $app
         * @return bool
         * @throws ApplicationException
         */
        public function filter(ApplicationInterface $app) : bool
        {
            // check route filter
            if ($this->getFilter() != '*')
            {

                $request = $app->getRequest();

                if (!$request)
                {
                    throw new ApplicationException('Cannot run RouteFilter: no request has been set');
                }

                $route = $request->getRoute();

                if (!$route)
                {
                    throw new ApplicationException('Cannot run RouteFilter: no route has been set');
                }


                if (!$app->getRouteMatcher()->match($this->getFilter(), $route))
                {
                    return false;
                }
            }

            return true;
        }

        /**
         * @return string
         */
        public function getDescription() : string
        {
            return 'Route Filter (' . get_class($this) . ')';
        }


    }
