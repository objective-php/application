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
        
        return $response->withStatus($exception->getCode() ?: 500);
        
    }

}
