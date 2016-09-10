<?php
/**
 * Created by PhpStorm.
 * User: gauthier
 * Date: 23/04/2016
 * Time: 10:38
 */

namespace Test\ObjectivePHP\Application\Middleware;


use ObjectivePHP\Application\AbstractApplication;
use ObjectivePHP\Application\Action\RestfulAction;
use ObjectivePHP\Application\Middleware\Exception;
use ObjectivePHP\Message\Request\HttpRequest;
use ObjectivePHP\Message\Request\Parameter\Container\HttpParameterContainer;
use ObjectivePHP\PHPUnit\TestCase;

class AbstractRestfulActionTest extends TestCase
{

    public function testRouting()
    {

        $restfulMiddleware = new GetOnlyRestMiddleware();
        $restfulMiddleware->setApplication($this->getApplication('get'));

        $reference = $restfulMiddleware->route();

        $this->assertEquals('get', $reference);

        $this->assertEquals([$restfulMiddleware, 'get'], $restfulMiddleware->getMiddleware($reference));

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
     * @return AbstractApplication
     */
    protected function getApplication($method)
    {
        $application = $this->createMock(AbstractApplication::class);

        $request = $this->createMock(HttpRequest::class);
        $request->method('getGet')->willReturn([]);
        $request->method('getPost')->willReturn([]);
        $request->method('getMethod')->willReturn($method);


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
