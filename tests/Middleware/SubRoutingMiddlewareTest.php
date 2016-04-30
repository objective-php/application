<?php
/**
 * Created by PhpStorm.
 * User: gauthier
 * Date: 23/04/2016
 * Time: 09:18
 */

namespace Test\ObjectivePHP\Application\Middleware;


use ObjectivePHP\Application\ApplicationInterface;
use ObjectivePHP\Application\Middleware\Exception;
use ObjectivePHP\Application\Middleware\MiddlewareInterface;
use ObjectivePHP\Application\Middleware\SubRoutingMiddleware;
use ObjectivePHP\PHPUnit\TestCase;

class SubRoutingMiddlewareTest extends TestCase
{

    public function testMiddlewareStack()
    {

        /** @var SubRoutingMiddleware $subRoutingMiddleware */
        $subRoutingMiddleware = $this->getMockForAbstractClass(SubRoutingMiddleware::class);
        $subRoutingMiddleware->expects($this->once())->method('route')->willReturn('first');

        $app = $this->getMock(ApplicationInterface::class);

        $firstMiddleware = $this->getMock(MiddlewareInterface::class);
        $firstMiddleware->expects($this->once())->method('__invoke')->with($app);

        $subRoutingMiddleware->registerMiddleware('first', $firstMiddleware);

        $result = $subRoutingMiddleware->getMiddleware('first');

        $this->assertSame($firstMiddleware, $result);

        $subRoutingMiddleware($app);

    }

    public function testSubRoutingExecutionFailsIfNoMatchingMiddlewareIsRegistered()
    {
        /** @var SubRoutingMiddleware $subRoutingMiddleware */
        $subRoutingMiddleware = $this->getMockForAbstractClass(SubRoutingMiddleware::class);

        $subRoutingMiddleware->expects($this->once())->method('route');

        $this->expectsException(function() use($subRoutingMiddleware) {
            $subRoutingMiddleware($this->getMockForAbstractClass(ApplicationInterface::class));
        }, Exception::class);

    }
    
}