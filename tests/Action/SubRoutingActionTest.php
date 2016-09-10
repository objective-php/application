<?php
/**
 * Created by PhpStorm.
 * User: gauthier
 * Date: 02/05/2016
 * Time: 13:56
 */

namespace Tests\ObjectivePHP\Application\Action;


use ObjectivePHP\Application\Action\SubRoutingAction;
use ObjectivePHP\Application\ApplicationInterface;
use ObjectivePHP\Application\Middleware\AbstractMiddleware;
use ObjectivePHP\PHPUnit\TestCase;
use ObjectivePHP\ServicesFactory\ServicesFactory;

class SubRoutingActionTest extends TestCase
{
    public function testServicesFactoryIsCalledToInjectDependencies()
    {
        $middleware = $this->createMock(AbstractMiddleware::class);

        $servicesFactory = $this->createMock(ServicesFactory::class);
        $servicesFactory->expects($this->once())->method('injectDependencies')->with($middleware);

        $application = $this->createMock(ApplicationInterface::class);
        $application->method('getServicesFactory')->willReturn($servicesFactory);

        $subRoutingAction = $this->getMockBuilder(SubRoutingAction::class)->setMethods(['route', 'getMiddleware'])->getMockForAbstractClass();
        $subRoutingAction->expects($this->once())->method('route')->willReturn('test');
        $subRoutingAction->expects($this->once())->method('getMiddleware')->with('test')->willReturn($middleware);

        $subRoutingAction->__invoke($application);

    }
}
