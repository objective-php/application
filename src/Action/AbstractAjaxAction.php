<?php

namespace ObjectivePHP\Application\Action;


use ObjectivePHP\Application\Action\HttpAction;
use ObjectivePHP\Application\Action\SubRoutingAction;
use ObjectivePHP\Application\Middleware\AbstractMiddleware;
use Zend\Diactoros\Response\JsonResponse;

/**
 * Class AbstractRestfulAction
 * @package ObjectivePHP\Application\Middleware
 */
abstract class AbstractAjaxAction extends AbstractMiddleware
{

    use HttpAction;

    /**
     * @param array ...$args
     * @return mixed|JsonResponse
     */
    public function __invoke(...$args)
    {
        $response = parent::__invoke(...$args);
        $this->getApplication()->setParam('layout.name', false);
        return ($response instanceof JsonResponse) ? $response : new JsonResponse($response);
    }

    /**
     * @return mixed
     */
    public function route()
    {
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