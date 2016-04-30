<?php

namespace ObjectivePHP\Application\Middleware;


use ObjectivePHP\Application\Action\HttpAction;
use ObjectivePHP\Application\Action\SubRoutingAction;
use Zend\Diactoros\Response\JsonResponse;

abstract class AbstractRestfulAction extends SubRoutingAction
{

    use HttpAction;

    public function __invoke(...$args)
    {
        $response = parent::__invoke(...$args);

        return ($response instanceof JsonResponse) ? $response : new JsonResponse($response);
    }

    public function route()
    {
        $verb = $this->getApplication()->getRequest()->getMethod();

        return strtolower($verb);

    }

    public function getMiddleware($reference)
    {
        if(method_exists($this, $reference))
        {
            return [$this, $reference];
        }
    }

}