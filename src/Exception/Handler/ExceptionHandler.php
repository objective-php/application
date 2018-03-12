<?php
/**
 * Created by PhpStorm.
 * User: gde
 * Date: 12/03/2018
 * Time: 19:34
 */

namespace ObjectivePHP\Application\Exception;


use ObjectivePHP\Application\Middleware\MiddlewareRegistry;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ExceptionHandler implements RequestHandlerInterface
{

    /**
     * @var MiddlewareRegistry
     */
    protected $exceptionProcessors;



    /**
     * Handle the request and return a response.
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->exceptionProcessors->next()->process($request, $this);
    }

    public function registerExceptionProcessor(ExceptionProcessorInterface $processor)
    {
        $this->exceptionProcessors->append($processor);
    }


}