<?php
/**
 * Created by PhpStorm.
 * User: gde
 * Date: 12/03/2018
 * Time: 19:35
 */

namespace ObjectivePHP\Application\Exception\Handler;


use ObjectivePHP\Middleware\Action\PhtmlAction\PhtmlAction;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class DefaultExceptionRenderer extends PhtmlAction
{
    /**
     * Process an incoming server request and return a response, optionally delegating
     * response creation to a handler.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $exception = $request->getAttribute('exception');
        $response = $this->render(compact('exception'));
        $code = $exception->getCode() ?: 500;
        try {
            $response = $response->withStatus($code);
        } catch (\InvalidArgumentException $e) {
            // force status to 500 if Exception status code is not valid
            $response = $response->withStatus(500);
        }
        
        
        return $response;
    }

}
