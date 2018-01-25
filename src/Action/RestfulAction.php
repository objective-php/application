<?php

namespace ObjectivePHP\Application\Action;


use ObjectivePHP\Application\Action\HttpAction;
use ObjectivePHP\Application\Action\SubRoutingAction;
use Zend\Diactoros\Response\JsonResponse;
use ObjectivePHP\Primitives\String\Str;

/**
 * Class AbstractRestfulAction
 * @package ObjectivePHP\Application\Middleware
 */
abstract class RestfulAction extends SubRoutingAction
{

    /**
     * @param array ...$args
     * @return mixed|JsonResponse
     */
    public function __invoke(...$args)
    {
        $response = parent::__invoke(...$args);

        return ($response instanceof JsonResponse) ? $response : new JsonResponse($response);
    }

    /**
     * @return mixed
     */
    public function route()
    {
        $methodName = (new Str($this->getApplication()->getRequest()->getMatchedRoute()->getName()))
            ->snakeCase()
            ->camelCase()
            ->getInternalValue();

        $callable = $this->getCallable();
        if (method_exists($callable, $methodName)) {
            return $methodName;
        }

        $verb = $this->getApplication()->getRequest()->getMethod();

        return strtolower($verb);

    }

    /**
     * @param $reference
     * @return array
     */
    public function getMiddleware($reference)
    {
        if(method_exists($this, $reference))
        {
            return [$this, $reference];
        }
    }

}