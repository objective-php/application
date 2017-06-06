<?php

namespace ObjectivePHP\Application\Operation;

use ObjectivePHP\Application\ApplicationInterface;
use ObjectivePHP\Application\Config\LayoutsLocation;
use ObjectivePHP\Application\Config\ViewsLocation;
use ObjectivePHP\Application\Exception;
use ObjectivePHP\Application\Middleware\AbstractMiddleware;
use ObjectivePHP\Application\View\Helper\Vars;
use ObjectivePHP\Html\Tag\Tag;

/**
 * Class ViewRenderer
 *
 * @package ObjectivePHP\Application\Task\Common
 */
class ViewRenderer extends AbstractMiddleware
{

    /**
     * @var ApplicationInterface
     */
    protected $application;
    
    /**
     * @var callable
     */
    protected $errorHandler;
    
    /**
     * @param ApplicationInterface $app
     *
     * @throws Exception
     */
    public function run(ApplicationInterface $app)
    {

        $this->setApplication($app);

        $viewName = $this->getViewName($app);
        if(!$viewName)
        {
            return;
        }

        $viewPath = $this->resolveViewPath($viewName);

        $app->setParam('view.script', $viewPath);

        $output = $this->render($viewPath);

        // handle layout
        if($layoutName = $this->getLayoutName())
        {
            $layoutPath = $this->resolveLayoutPath($layoutName);

            Vars::set('view.output', $output);
            $output = $this->render($layoutPath);
            Vars::unset('view.output');
        }

        $app->getResponse()->getBody()->rewind();
        $app->getResponse()->getBody()->write($output);
    }


    /**
     * @param ApplicationInterface $app
     *
     * @return mixed
     */
    protected function getViewName(ApplicationInterface $app)
    {
        return $app->getParam('view.template');
    }


    /**
     * @param       $viewName
     * @param array $context
     *
     * @return string
     * @throws Exception
     */
    public function render($viewPath, $vars = [])
    {

        foreach($vars as $reference => $value)
        {
            Vars::set($reference, $value);
        }

        if(!file_exists($viewPath))
        {
            throw new Exception(sprintf('View script "%s" does not exist', $viewPath));
        }


        ob_start();
        // deactivate error handler during rendering
        $previousErrorHandler = set_error_handler($this->getErrorHandler());
        include $viewPath;
        // restore previous error handler
        set_error_handler($previousErrorHandler);
        $output = ob_get_clean();

        return $output;
    }

    /**
     * @param $viewName
     *
     * @return callable
     */
    protected function resolveViewPath($viewName)
    {

        $viewPath = $viewName . '.phtml';

        if(!is_file($viewPath)) throw new Exception(sprintf('View script "%s" does not exist', $viewPath));

        return $viewPath;
    }

    /**
     * @return array
     */
    protected function getViewsLocations()
    {
        $config = $this->getApplication()->getConfig();

        return $config->get(ViewsLocation::class, []);

    }

    /**
     * @return array
     */
    protected function getLayoutsLocations()
    {
        $config = $this->getApplication()->getConfig();

        return $config->get(LayoutsLocation::class, []);

    }

    /**
     * @return string
     */
    protected function getLayoutName()
    {
        // FIXME layout.default config directive does not exist
        $layout = $this->getApplication()->getParam('layout.name');

        if($layout === false)
        {
            return null;
        }

        if(is_null($layout))
        {
            $layout = $this->getApplication()->getConfig()->get('layouts.default', 'layout');
        }

        return $layout;
    }

    /**
     * @param $layoutName
     *
     * @return callable
     */
    protected function resolveLayoutPath($layoutName)
    {

        $layoutsLocations = $this->getLayoutsLocations();

        foreach($layoutsLocations as $location)
        {
            $layoutPath = $location . '/' . $layoutName . '.phtml';
            if(file_exists($layoutPath))
            {
                // make layout path available to the rest of the application
                $this->getApplication()->setParam('layout.script', $layoutPath);

                return $layoutPath;
            }
        }

        // no mathcing layout script has been found
        throw new Exception(sprintf('No layout script matching layout name "%s" has been found (layouts locations: %s)', $layoutName, implode($layoutsLocations)));
    }

    public function errorHandler($level, $message, $file, $line)
    {
        $levelLabel = '';
        $color = '#000';
        switch($level)
        {
            case 1:
            case 16:
            case 64:
            case 256:
            case 4096:
                $levelLabel = 'error';
                $color = '#F00';
                break;
                
            case 2:
            case 32:
            case 128:
            case 512:
                $color = '#FA0';
                $levelLabel = 'warning';
                break;
            
            case 4:
                $color = '#F00';
                $levelLabel = 'parse';
                break;
            
            case 8:
            case 1024:
                $color = '#FAF';
                $levelLabel = 'notice';
                break;
                
            case 2048:
                $levelLabel = 'strict';
                break;
                
            case 8192:
            case 16384:
                $levelLabel = 'deprecated';
                break;
            
        }
        
        $file = ltrim(str_replace(getcwd(), '', $file), '/\\');
        
        Tag::span('[' . $levelLabel . '] ' . $file . ':' . $line . ' => ' . $message . '<br>')['style'] = 'color: ' . $color . ';font-weight:bold';
    }

    public function getErrorHandler()
    {
        if(is_null($this->errorHandler)) {
            return [$this, 'errorHandler'];
        }
        
    }
}
