<?php

namespace ObjectivePHP\Application\Bridge\Psr;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Interface RequestHandlerInterface
 * @package ObjectivePHP\Application
 */
interface RequestHandlerInterface
{
    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface;
}
