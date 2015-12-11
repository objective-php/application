<?php

    namespace ObjectivePHP\Application\Workflow\Filter;

    use ObjectivePHP\Application\ApplicationInterface;
    use ObjectivePHP\Application\Exception;
    use ObjectivePHP\Invokable\InvokableInterface;

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
        public function __invoke(ApplicationInterface $app) : bool
        {
            // check route filter
            if ($this->getFilter() != '*')
            {

                $request = $app->getRequest();

                if (!$request)
                {
                    throw new Exception(sprintf('Cannot run UrlFilter for filter "%s": no request has been set', $this->getFilter()));
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

        /**
         * @return string
         */
        public function getDescription() : string
        {
            return 'Url Filter (' . get_class($this) . ')';
        }

    }