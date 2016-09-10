<?php

namespace ObjectivePHP\Application\Operation;


use ObjectivePHP\Application\ApplicationInterface;
use ObjectivePHP\Application\Workflow\Hook;
use ObjectivePHP\Html\Tag\Tag;
use ObjectivePHP\Primitives\String\Str;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\SapiEmitter;

/**
 * Class ExceptionHandler
 *
 * @package ObjectivePHP\Application\Task\Common
 */
class ExceptionHandler
{
    
    public function __construct()
    {
        set_error_handler([$this, 'errorHandler']);
    }
    
    public function errorHandler($errno, $errstr, $errfile, $errline)
    {
        $errnoToCatch = E_CORE_ERROR | E_ERROR | E_CORE_ERROR | E_PARSE | E_RECOVERABLE_ERROR | E_USER_ERROR | E_WARNING;
        
        if ($errno & $errnoToCatch)
        {
            throw new \ErrorException($errstr, $errno, 1, $errfile, $errline);
        }
        /*
        else {
            // forward error
            trigger_error($errstr, E_USER_ERROR);
        }
        */
    }
    
    /**
     * @param ApplicationInterface $app
     */
    public function __invoke(ApplicationInterface $app)
    {
        $exception = $app->getException();
    
        if (php_sapi_name() == 'cli')
        {
            throw $exception;
        }
        else
        {
        $output = Tag::h1('An error occurred');
        
        do
        {
            $output .= $this->renderException($exception);
        } while ($exception = $exception->getPrevious());
        
        $output .= Tag::h2('Workflow');
        
        foreach ($app->getExecutionTrace() as $step => $middlewares)
        {
            $output .= Tag::h3('Step: ' . $step);
            
            /**
             * @var Hook $hook
             */
            foreach ($middlewares as $middleware)
            {
                $output .= Tag::dt([$middleware->getReference() . ': ', $middleware->getDescription()]);
            }
        }
        
        // display config
        $output .= Tag::h2('Configuration');
        ob_start();
        var_dump($app->getConfig()->getInternalValue());
        $output .= ob_get_clean();
        
        // display services
        $output .= Tag::h2('Services');
        foreach ($app->getServicesFactory()->getServices() as $spec)
        {
            $output .= Tag::h3($spec->getId());
            ob_start();
            var_dump($spec);
            $output .= ob_get_clean();
        }
        
        // manually emit response
        
            (new SapiEmitter())->emit((new HtmlResponse($output))->withStatus(500));
        }
        
    }
    
    protected function renderException(\Throwable $exception)
    {
        $div = Tag::div(Tag::h2('Exception trace'), 'errors');
        
        
        $div->append(Tag::h2('Exception'), Tag::i(get_class($exception)));
        $div->append(Tag::h2('Message'), Tag::pre($exception->getMessage()));
        $div->append(Tag::h2('File'), Tag::pre($exception->getFile())->append(':', $exception->getLine())
                                         ->setSeparator(''));
        
        // shorten Trace
        $trace = Str::cast($exception->getTraceAsString())->replace(getcwd() . '/', '');
        
        
        $div->append(Tag::h2('Trace'), Tag::pre($trace));
        
        return $div;
    }
}
