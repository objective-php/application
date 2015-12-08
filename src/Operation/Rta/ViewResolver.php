<?php

    namespace ObjectivePHP\Application\Operation\Rta;
    
    
    use ObjectivePHP\Application\Action\RenderableActionInterface;
    use ObjectivePHP\Application\ApplicationInterface;
    use ObjectivePHP\Application\Middleware\AbstractMiddleware;
    use ObjectivePHP\Application\Workflow\Event\WorkflowEvent;
    use ObjectivePHP\Primitives\Collection\Collection;
    use ObjectivePHP\Primitives\String\Str;
    use ObjectivePHP\ServicesFactory\ServiceReference;

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
         * @param Application $app
         *
         * @return $this|mixed|null
         */
        public function run(ApplicationInterface $app)
        {

            $this->setApplication($app);

            $app->setParam('view.template', $this->getViewTemplate());

        }

        /**
         * @return $this|mixed|null
         */
        public function getViewTemplate()
        {
            // get action
            $action = $this->getApplication()->getParam('action');


            if($action instanceof ServiceReference)
            {
                $action = $this->getApplication()->getServicesFactory()->get($action);
            }

            if (!$action instanceof RenderableActionInterface)
            {
                return null;
            }

            return $action->getViewTemplate();


        }

        /**
         * @return ApplicationInterface
         */
        public function getApplication()
        {
            return $this->application;
        }

        /**
         * @param ApplicationInterface $application
         *
         * @return $this
         */
        public function setApplication($application)
        {
            $this->application = $application;

            return $this;
        }

    }