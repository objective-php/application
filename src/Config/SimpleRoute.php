<?php
/**
 * This file is part of the Objective PHP project
 *
 * More info about Objective PHP on www.objective-php.org
 *
 * @license http://opensource.org/licenses/GPL-3.0 GNU GPL License 3.0
 */

namespace ObjectivePHP\Application\Config;


use ObjectivePHP\Config\SingleValueDirectiveGroup;
use ObjectivePHP\Message\Request\RequestInterface;

class SimpleRoute extends SingleValueDirectiveGroup
{
    /**
     * @var
     */
    protected $action;

    /**
     * @var callable
     */
    protected $pathHelper;

    public function __construct($route, $path, $action = null)
    {

        if(!is_callable($path))
        {
            $path = function(RequestInterface $request = null, $params = []) use($path) {

                // no request is passed, so forge matching URL
                if(is_null($request)) {
                    return $path;
                }

                // else try to match Url
                return $path == $request->getUri()->getPath();

            };
        }

        $this->setAction($action);
        $this->pathHelper = $path;

        parent::__construct($route, $this);
    }

    public function matches(RequestInterface $request)
    {
        /** @var callable $pathHelper */
        $pathHelper = $this->pathHelper;
        return $pathHelper($request);
    }

    /**
     * @return mixed
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param mixed $action
     */
    public function setAction($action)
    {
        $this->action = $action;
    }

}