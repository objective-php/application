<?php

    namespace ObjectivePHP\Application\Pattern\Rta;
    
    
    use ObjectivePHP\Application\Common\Renderer\LayoutRenderer;
    use ObjectivePHP\Application\Common\Renderer\ViewRenderer;
    use ObjectivePHP\Application\Common\RequestFactory;
    use ObjectivePHP\Application\Common\PackagesLoader;
    use ObjectivePHP\Application\Pattern\Rta\Router\RtARouter;
    use ObjectivePHP\Application\Workflow\Event\WorkflowEvent;
    use ObjectivePHP\Events\EventsHandler;

    class RtaCallbacksAggregate
    {

        public function __invoke(WorkflowEvent $event)
        {
            $application = $event->getApplication();
            $workflow = $application->getWorkflow();

            $workflow->bind('packages.pre', PackagesLoader::class);
            $workflow->bind('packages.post', RequestFactory::class);
            $workflow->bind('run.route', RtaRouter::class);
            $workflow->bind('run.render', ViewRenderer::class);

            // layout
            $layoutRenderer = new LayoutRenderer();
            $workflow->bind('run.pre', [$layoutRenderer, 'captureView'], EventsHandler::BINDING_MODE_FIRST);
            $workflow->bind('run.post', [$layoutRenderer, 'renderLayout']);

        }

    }