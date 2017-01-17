<?php

namespace ObjectivePHP\Application\Operation;


use ObjectivePHP\Application\{
    ApplicationInterface, Exception
};
use Zend\Diactoros\{
    Response,
    Response\HtmlResponse,
    Response\SapiEmitter
};

/**
 * Class ExceptionHandler
 *
 * @package ObjectivePHP\Application\Task\Common
 */
class ExceptionHandler
{
    /** @var ApplicationInterface */
    protected $app;

    /** @var \Throwable */
    protected $exception;

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

    public function __invoke(ApplicationInterface $app)
    {
        $this->app = $app;
        $exception = $this->app->getException();

        if ($exception instanceof Exception
            && preg_match('/^Failed running hook "(.*)" of type:/', $exception->getMessage(), $m)
            && $exception->getPrevious()
        ) {
            $this->exception = $exception->getPrevious();
        } else {
            $this->exception = $exception;
        }

        $code = ($this->exception->getCode() < 100 || $this->exception->getCode() > 599) ? 500 : $this->exception->getCode();
        $this->app->setResponse((new Response())->withStatus($code));

        if (php_sapi_name() == 'cli') {
            throw $exception;
        }

        $exceptions = '';
        $exception = $this->exception;
        $config = !empty(getenv('APP_ENV')) && !in_array(getenv('APP_ENV'), ['prod', 'production']) ? $this->renderConfig() : '';

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
                    <div>' . $config . '</div>
                    
                    ' . $this->renderJs() . '
                </body>
                            
            </html>
        ';


        // manually emit response
        (new SapiEmitter())->emit((new HtmlResponse($output))->withStatus($code));
    }


    protected function renderException(\Throwable $exception)
    {
        $output = '
            <div class="quote">
                <h2> ' . get_class($exception) .
            '</h2> <h3>' . $exception->getMessage() . '</h3>
            </div>';

        $output .= '
            <div class="exception-details">
                <div>
                     <strong>' . $this->app->getResponse()->getStatusCode() . '</strong>
                      - ' . $this->app->getResponse()->getReasonPhrase() . '
                   
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

    protected function renderConfig()
    {
        $html = '<table>';

        foreach ($this->app->getConfig()->toArray() as $item => $value) {
            $html .= '<tr>';
            $html .= "<td class=''>{$item}</td>";
            $html .= "<td class=''><pre>" . json_encode($value, JSON_PRETTY_PRINT) . "</pre></td>";
            $html .= '</tr>';

        }
        $html .= '</table>';

        // display config
        $output = '
            <div class="config">
                <div class="quote togglable"><h2>Configuration (click to show)</h2></div>
                <div class="config panel">' . $html . '</div>
            </div>'
        ;

        return $output;
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
