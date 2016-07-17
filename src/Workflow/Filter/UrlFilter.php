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
         * @param ApplicationInterface $app
         * @return bool
         * @throws Exception
         */
        public function run(ApplicationInterface $app) : bool
        {
            // deactivation Url filtered middleware if running in CLI
            // TODO add support for CLI requests
            if(php_sapi_name() === 'cli')
            {
                return false;
            }

            // check route filter
            if ($this->getFilter() != '*')
            {

                $request = $this->getApplication()->getRequest();

                if (!$request)
                {
                    throw new Exception(sprintf('Cannot run UrlFilter for filter "%s": no request has been set', $this->getFilter()));
                }

                // use route as reference to match route filter, but default to URL if
                // no route has been set yet
                $path = $request->getUri()->getPath();

                if (!$this->getApplication()->getRouteMatcher()->match($this->getFilter(), $path))
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
            return 'Url Filter (' . get_class($this) . ')';
        }

    }
