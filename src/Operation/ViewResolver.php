<?php

namespace ObjectivePHP\Application\Operation;


use ObjectivePHP\Application\Action\RenderableActionInterface;
use ObjectivePHP\Application\ApplicationInterface;
use ObjectivePHP\Application\Middleware\AbstractMiddleware;

/**
 * Class ViewResolver
 *
 * @package ObjectivePHP\Application\Task\Rta
 */
class ViewResolver extends AbstractMiddleware
{
    /**
     * @var ApplicationInterface
     */
    protected $application;

    /**
     * @param ApplicationInterface $app
     *
     * @return $this|mixed|null
     */
    public function run(ApplicationInterface $app)
    {
        $this->setApplication($app);

        $app->setParam('view.template', $this->getViewTemplate());
    }

    /**
     * @return mixed
     */
    public function getViewTemplate()
    {
        // get action
        $actionMiddleware = $this->getApplication()->getParam('runtime.action.middleware');

        $action = $actionMiddleware->getCallable($this->getApplication());

        if(!$action instanceof RenderableActionInterface)
        {
            return null;
        }

        return $action->getViewTemplate();


    }

}
