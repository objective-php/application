<?php
/**
 * Created by PhpStorm.
 * User: gauthier
 * Date: 23/04/2016
 * Time: 09:18
 */

namespace Test\ObjectivePHP\Application\Middleware;


use ObjectivePHP\Application\Action\SubRoutingAction;
use ObjectivePHP\Application\ApplicationInterface;
use ObjectivePHP\Application\Middleware\Exception;
use ObjectivePHP\Application\Middleware\MiddlewareInterface;
use ObjectivePHP\PHPUnit\TestCase;

class SubRoutingMiddlewareTest extends TestCase
{

    public function testMiddlewareStack()
    {

        /** @var SubRoutingAction $subRoutingMiddleware */
        $subRoutingMiddleware = $this->getMockForAbstractClass(SubRoutingAction::class);
        $subRoutingMiddleware->expects($this->once())->method('route')->willReturn('first');

        $app = $this->createMock(ApplicationInterface::class);

        $firstMiddleware = $this->createMock(MiddlewareInterface::class);
        $firstMiddleware->expects($this->once())->method('__invoke')->with($app);

        $subRoutingMiddleware->registerMiddleware('first', $firstMiddleware);

        $result = $subRoutingMiddleware->getMiddleware('first');

        $this->assertSame($firstMiddleware, $result);

        $subRoutingMiddleware($app);

    }

    public function testSubRoutingExecutionFailsIfNoMatchingMiddlewareIsRegistered()
    {
        /** @var SubRoutingAction $subRoutingMiddleware */
        $subRoutingMiddleware = $this->getMockForAbstractClass(SubRoutingAction::class);

        $subRoutingMiddleware->expects($this->once())->method('route');

        $this->expectsException(function() use($subRoutingMiddleware) {
            $subRoutingMiddleware($this->getMockForAbstractClass(ApplicationInterface::class));
        }, Exception::class);

    }
    
}
