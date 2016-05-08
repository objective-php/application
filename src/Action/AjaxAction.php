<?php

namespace ObjectivePHP\Application\Action;


use Zend\Diactoros\Response\JsonResponse;

/**
 * Class AbstractRestfulAction
 * @package ObjectivePHP\Application\Middleware
 */
abstract class AjaxAction extends HttpAction
{

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


}