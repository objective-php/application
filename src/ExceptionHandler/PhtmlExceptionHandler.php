<?php

namespace ObjectivePHP\Application\ExceptionHandler;


use ObjectivePHP\Application\{
    ApplicationInterface
};
use ObjectivePHP\Middleware\Action\PhtmlAction\PhtmlAction;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\{
    Response, Response\HtmlResponse
};

/**
 * Class ExceptionHandler
 *
 * @package ObjectivePHP\Application\Task\Common
 */
class PhtmlExceptionHandler extends PhtmlAction
{
    /** @var ApplicationInterface */
    protected $app;

    /** @var \Throwable */
    protected $exception;

    /** @var false|string $outputBuffer output buffer content produced before the uncaught exception is thrown */
    protected $outputBuffer;

    /** @var ServerRequestInterface */
    protected $request;

    /** @var int HTTP Response status code */
    protected $statusCode;


    public function __construct()
    {
        set_error_handler([$this, 'errorHandler']);
    }

    public function errorHandler($errno, $errstr, $errfile, $errline)
    {
        $errnoToCatch = E_CORE_ERROR | E_ERROR | E_CORE_ERROR | E_PARSE | E_RECOVERABLE_ERROR | E_USER_ERROR | E_WARNING;

        if ($errno & $errnoToCatch) {
            throw new \ErrorException($errstr, $errno, 1, $errfile, $errline);
        }
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->app = $handler;
        $this->request = $request;

        $this->outputBuffer = ob_get_clean();
        $exception = $request->getAttribute('exception');

        $this->statusCode = ($exception->getCode() < 100 || $exception->getCode() > 599) ? 500 : $exception->getCode();

        $exceptions = '';

        do {
            $exceptions .= '<div class="exception-def">' . $this->renderException($exception) . '</div>';
        } while ($exception = $exception->getPrevious());


        $output = '
            <!DOCTYPE html>
            <html>
                <head>
                    <meta charset="utf-8">
                    <meta http-equiv="X-UA-Compatible" content="IE=edge, chrome=1">
                    <title>An Exception occured</title>
                    
                    ' . $this->renderCss() . '
                </head>
                
                <body>
                    <div>' . $exceptions . '</div>
                    
                    ' . $this->renderJs() . '
                </body>
                            
            </html>
        ';

        // manually emit response
        return (new HtmlResponse($output))->withStatus($this->getStatusCode());
    }

    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @param int $statusCode
     */
    public function setStatusCode(int $statusCode)
    {
        $this->statusCode = $statusCode;
    }


    protected function renderException(\Throwable $exception)
    {
        $tmpResponse = (new Response())->withStatus($this->getStatusCode());

        $output = '
            <div class="quote">
                <h2> ' . get_class($exception) .
            '</h2> <h3>' . $exception->getMessage() . '</h3>
            </div>';

        $output .= '
            <div class="exception-details">
                <div>
                     <strong>' . $this->getStatusCode() . '</strong>
                      - ' . $tmpResponse->getReasonPhrase() . '
                   
                </div>
                <div>' . $exception->getFile() . ' line ' . $exception->getLine() . '</div>
                
                <div class="stacktrace-def">' . $this->renderStackTrace($exception) . '</div>
            </div>
        ';

        return $output;
    }

    protected function renderStackTrace(\Throwable $exception)
    {
        $stacktrace = nl2br(str_replace(getcwd() . '/', '/', $exception->getTraceAsString()));

        return '
            <div class="quote togglable"><h4 class="stack-title">Stack trace</h4></div>
            <div class="stacktrace panel">
                ' . $stacktrace . '
            </div>
        ';
    }

    protected function renderCss()
    {
        return '
        <style type="text/css">
            html, body {
              font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
              height: 100%;
              background-color: whitesmoke;
            }
            body {
              color: black;
              text-align: left;
            }
            .exception-def, .stacktrace-def, .config {
                margin: 2% 3%;
                background-color: white;
                border-radius: 4px;
                border: 2px solid #ddd;
            }
            
            .exception-def, .stacktrace-def {
                -webkit-box-shadow: 0 5px 5px rgba(0, 0, 0, .05);
                    box-shadow: 0 5px 5px rgba(0, 0, 0, .05);
            }
            
            .quote {
                padding: 10px 15px;
                border-bottom: 1px solid #ddd;
                border-top-left-radius: 3px;
                border-top-right-radius: 3px;
                background-color: white;
            }
         
            
            .exception-def .exception-details {
                padding: 10px;
            }
            
            .stacktrace {
                padding: 10px;
                font-family: monospace;
            }
            
            table, th, td, .panel, .togglable {
                border: 1px solid #ddd;
                border-top-left-radius: 3px;
                border-top-right-radius: 3px;
                border-collapse: collapse;
                padding: 5px;
            }
            
            table {
                margin-left: 5%;
            }
          
            
            .togglable {
                background-color: white;
                cursor: pointer;
                padding: 10px;
                text-align: left;
                transition: 0.4s;
            }
            
            .togglable:hover, .active {
                background-color: #c8c8c8; 
            }
            
            .panel {
                border: none;
                border-collapse: collapse;
                padding: 10px;
                display: none;
                background-color: white;
            }
            
            .panel.show {
                display: block;
            }
            
            .stack-title {
                margin: 2px;
            }
           
            
        </style>
        ';
    }

    protected function renderJs()
    {
        return '
        <script>
            var acc = document.getElementsByClassName("togglable");
            
            for (var i = 0; i < acc.length; i++) {
                acc[i].onclick = function(){
                    this.classList.toggle("active");
                    this.nextElementSibling.classList.toggle("show");
              }
            }
            
            var firstStack = document.getElementsByClassName("quote togglable")[0];
            firstStack.classList.toggle("active");
            firstStack.nextElementSibling.classList.toggle("show");
        </script>        
        ';
    }
}
