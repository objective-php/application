<?php

namespace ObjectivePHP\Application\Operation;


use ObjectivePHP\Application\ApplicationInterface;
use ObjectivePHP\Application\Exception;
use ObjectivePHP\Application\Middleware\AbstractMiddleware;
use ObjectivePHP\Cli\Request\CliRequest;
use ObjectivePHP\Message\Request\HttpRequest;
use Psr\Http\Message\StreamInterface;
use Zend\Diactoros\PhpInputStream;
use Zend\Diactoros\ServerRequestFactory;

/**
 * Class RequestWrapper
 *
 * @package ObjectivePHP\Application\Operation\Common
 */
class RequestWrapper extends AbstractMiddleware
{

    /** @var StreamInterface|resource|string */
    protected $stream;

    /**
     * Get Stream
     *
     * @return StreamInterface|resource|string
     */
    public function getStream()
    {
        if (empty($this->stream)) {
            $this->stream = new PhpInputStream();
        }

        return $this->stream;
    }

    /**
     * Set Stream
     *
     * @param StreamInterface|resource|string $stream
     *
     * @return $this
     */
    public function setStream($stream)
    {
        $this->stream = $stream;

        return $this;
    }

    /**
     * @param ApplicationInterface $app
     *
     * @throws Exception
     */
    public function run(ApplicationInterface $app)
    {
        if (isset($_SERVER['REQUEST_URI']))
        {
            $headers = ServerRequestFactory::marshalHeaders($_SERVER);
            $uri = ServerRequestFactory::marshalUriFromServer($_SERVER, $headers);

            $request = new HttpRequest($uri, $_SERVER['REQUEST_METHOD'], $this->getStream(), $headers);

            $request->setGet($_GET);
            $request->setPost($_POST);

            if(isset($_FILES)) {
                $request->getParameters()->setFiles($_FILES);
            }
        }
        else if(class_exists(CliRequest::class))
        {
            $request = new CliRequest($_SERVER['argv'][1] ?? null);
        }
        else {
            throw new Exception("No request matches current environment");
        }

        $app->setRequest($request);
    }
}
