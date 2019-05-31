<?php

namespace ObjectivePHP\Application\Exception\Filter;

use ObjectivePHP\Application\ApplicationInterface;
use ObjectivePHP\Application\Exception\ApplicationException;

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
     * @throws ApplicationException
     */
    public function run(ApplicationInterface $app) : bool
    {
        // deactivation Url filtered middleware if running in CLI
        // TODO add support for CLI requests
        if (php_sapi_name() === 'cli') {
            return false;
        }

        // check route filter
        if ($this->getFilter() != '*') {
            $request = $app->getRequest();

            if (!$request) {
                throw new ApplicationException(
                    sprintf('Cannot run UrlFilter for filter "%s": no request has been set', $this->getFilter())
                );
            }

            // use route as reference to match route filter, but default to URL if
            // no route has been set yet
            $path = $request->getUri()->getPath();

            if (!$app->getRouteMatcher()->match($this->getFilter(), $path)) {
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
