<?php

namespace ObjectivePHP\Application\Operation;

use ObjectivePHP\Application\ApplicationInterface;
use ObjectivePHP\Application\Config\LayoutsLocation;
use ObjectivePHP\Application\Config\ViewsLocation;
use ObjectivePHP\Application\Exception;
use ObjectivePHP\Application\Middleware\AbstractMiddleware;
use ObjectivePHP\Application\View\Helper\Vars;

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
        include $viewPath;
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


}
