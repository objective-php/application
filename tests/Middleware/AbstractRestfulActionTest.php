<?php
/**
 * Created by PhpStorm.
 * User: gauthier
 * Date: 23/04/2016
 * Time: 10:38
 */

namespace Test\ObjectivePHP\Application\Middleware;


use ObjectivePHP\Application\AbstractHttpApplication;
use ObjectivePHP\Application\Action\RestfulAction;
use ObjectivePHP\Application\Middleware\Exception;
use ObjectivePHP\Message\Request\HttpRequest;
use ObjectivePHP\Message\Request\Parameter\Container\HttpParameterContainer;
use ObjectivePHP\PHPUnit\TestCase;
use ObjectivePHP\Router\MatchedRoute;

class AbstractRestfulActionTest extends TestCase
{

    public function testRoutingWhenMethodNameDoesNotExist()
    {
        $applicationMock = $this->getApplication('get');

        $restfulMiddleware = new GetOnlyRestMiddleware();
        $restfulMiddleware->setApplication($applicationMock);

        $reference = $restfulMiddleware->route();

        $this->assertEquals('get', $reference);

        $this->assertEquals([$restfulMiddleware, 'get'], $restfulMiddleware->getMiddleware($reference));

    }

    public function testRoutingWhenMethodNameDoesExist()
    {
        $applicationMock = $this->getApplication('get');

        $restfulMiddleware = new class extends RestfulAction {
            public function thisIsFakeName() {
                return ['data' => 'value'];
            }
        };

        $restfulMiddleware->setApplication($applicationMock);

        $reference = $restfulMiddleware->route();

        $this->assertEquals('thisIsFakeName', $reference);

        $this->assertEquals([$restfulMiddleware, 'thisIsFakeName'], $restfulMiddleware->getMiddleware($reference));

    }


    public function testJsonResponseIsAutomaticallyGeneratedFromMiddlewareReturn()
    {
        $restfulMiddleware = new GetOnlyRestMiddleware();
        $this->assertEquals(json_encode(['data' => 'value']),
            $restfulMiddleware($this->getApplication('get'))->getBody());

    }

    public function testExecutionFailsIfMethodMatchingHttpVerbIsNotImplemented()
    {
        $restfulMiddleware = new GetOnlyRestMiddleware();
        $restfulMiddleware->setApplication($this->getApplication('post'));
        $reference = $restfulMiddleware->route();

        $this->assertEquals('post', $reference);

        $this->expectsException(function () use ($restfulMiddleware)
        {
            $restfulMiddleware($this->getApplication('post'));
        }, Exception::class, 'post');
    }

    /**
     * @param $method
     * @return AbstractHttpApplication
     */
    protected function getApplication($method)
    {
        $application = $this->createMock(AbstractHttpApplication::class);

        $matchedRoute = $this->createMock(MatchedRoute::class);
        $matchedRoute->method('getName')->willReturn('this-is_a-fake_Name');

        $request = $this->createMock(HttpRequest::class);
        $request->method('getGet')->willReturn([]);
        $request->method('getPost')->willReturn([]);
        $request->method('getMethod')->willReturn($method);
        $request->method('getMatchedRoute')->willReturn($matchedRoute);

        $httpParameters = new HttpParameterContainer($request);
        $request->method('getParameters')->willReturn($httpParameters);

        $application->method('getRequest')->willReturn($request);

        return $application;
    }

}

// HELPERS
class GetOnlyRestMiddleware extends RestfulAction
{
    public function get()
    {
        return ['data' => 'value'];
    }

}
