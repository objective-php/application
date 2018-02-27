<?php

namespace Test\ObjectivePHP\Application\Middleware;

use ObjectivePHP\Application\AbstractApplication;
use ObjectivePHP\Application\Operation\RequestWrapper;
use ObjectivePHP\PHPUnit\TestCase;
use Zend\Diactoros\PhpInputStream;

class RequestWrapperTest extends TestCase
{
    /**
     * @var string
     */
    protected $file;

    /**
     * @var PhpInputStream
     */
    protected $stream;

    public function setUp()
    {
        $this->file = __DIR__ . '/../data/input-stream.txt';

        $this->stream = fopen($this->file, 'r');
    }

    /**
     * @throws \ObjectivePHP\Application\Package\Exception
     */
   public function testBodyContentsIfNotEmpty()
   {
       $_SERVER['REQUEST_URI'] = 'fake';

       $app = new class extends AbstractApplication {
           public function init()
           {
           }
       };

       $requestWrapper = new RequestWrapper();
       $requestWrapper->setStream(new PhpInputStream($this->stream));
       $requestWrapper->run($app);
       
       $request = $app->getRequest();

       $content = $request->getBody()->getContents();

       $request->getBody()->rewind();

       $this->assertEquals($content, $request->getbody()->getContents());
   }

    /**
     * @throws \ObjectivePHP\Application\Package\Exception
     */
    public function testBodyContentsIfStreamIsEmpty()
    {
        $_SERVER['REQUEST_URI'] = 'fake';

        $app = new class extends AbstractApplication {
            public function init()
            {
            }
        };

        $requestWrapper = new RequestWrapper();
        $requestWrapper->run($app);

        $request = $app->getRequest();

        $content = $request->getBody()->getContents();

        $this->assertEquals($content, '');
    }
}
