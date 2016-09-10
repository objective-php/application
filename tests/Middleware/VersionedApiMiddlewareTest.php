<?php
/**
 * Created by PhpStorm.
 * User: gauthier
 * Date: 23/04/2016
 * Time: 10:17
 */

namespace Test\ObjectivePHP\Application\Middleware;


use ObjectivePHP\Application\Action\VersionedApiAction;
use ObjectivePHP\Application\ApplicationInterface;
use ObjectivePHP\Application\Middleware\MiddlewareInterface;
use ObjectivePHP\Message\Request\HttpRequest;
use ObjectivePHP\Message\Request\Parameter\Container\HttpParameterContainer;
use ObjectivePHP\PHPUnit\TestCase;

class VersionnedApiMiddlewareTest extends TestCase
{
    
    public function testDefaultRouting()
    {

        $middleware = $this->getMockForAbstractClass(VersionedApiAction::class);
        $middleware->setApplication($this->getApplication());

        $version = $middleware->route();

        $this->assertEquals('1.0', $version);
    }


    public function testRouting()
    {

        $middleware = $this->getMockForAbstractClass(VersionedApiAction::class);
        $middleware->setApplication($this->getApplication(['version' => '2.0']));

        $version = $middleware->route();

        $this->assertEquals('2.0', $version);
        
    }
    
    public function testlistAvailableVersions()
    {
        $middleware = $this->getMockForAbstractClass(VersionedApiAction::class);
        
        $firstMiddleware = $this->createMock(MiddlewareInterface::class);
        $secondMiddleware = $this->createMock(MiddlewareInterface::class);
        
        $middleware->registerMiddleware('1.0', $firstMiddleware);
        $middleware->registerMiddleware('2.0', $secondMiddleware);
        
        $this->assertEquals(['1.0', '2.0'], $middleware->listAvailableVersions());
    }

    
    protected function getApplication($parameters = [])
    {
        $application = $this->createMock(ApplicationInterface::class);
        
        $request = $this->createMock(HttpRequest::class);
        $request->method('getGet')->willReturn($parameters);
        $request->method('getPost')->willReturn([]);


        $httpParameters = new HttpParameterContainer($request);
        $request->method('getParameters')->willReturn($httpParameters);

        $application->method('getRequest')->willReturn($request);

        return $application;
    }
}
