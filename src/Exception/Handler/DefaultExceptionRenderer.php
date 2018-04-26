<?php

namespace ObjectivePHP\Application\Exception\Handler;

use ObjectivePHP\Middleware\Action\PhtmlAction\PhtmlAction;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class DefaultExceptionRenderer
 *
 * @package ObjectivePHP\Application\Exception\Handler
 */
class DefaultExceptionRenderer extends PhtmlAction
{
    /**
     * Process an incoming server request and return a response, optionally delegating
     * response creation to a handler.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $exception = $request->getAttribute('exception');
        $buffer = $request->getAttribute('buffer');
        $headers = $request->getAttribute('headers');

        $response = $this->render(compact('exception', 'buffer', 'headers'));

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
