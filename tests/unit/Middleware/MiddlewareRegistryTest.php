<?php


namespace unit\Middleware;


use Codeception\TestCase\Test;
use ObjectivePHP\Application\Middleware\MiddlewareRegistry;
use Psr\Http\Server\MiddlewareInterface;

class MiddlewareRegistryTest extends Test
{

    public function testMiddlewareRegistration()
    {
        $registry = new MiddlewareRegistry();
        $middleware = $this->makeEmpty(MiddlewareInterface::class);

        $registry->registerMiddleware($middleware);

        $this->assertSame($middleware, $registry->getNextMiddleware());
    }
}
