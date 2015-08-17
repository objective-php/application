<?php

    namespace ObjectivePHP\Application\Pattern\Rta;
    
    
    use ObjectivePHP\Application\Common\Renderer\PhtmlRenderer;
    use ObjectivePHP\Application\Common\RequestFactory;
    use ObjectivePHP\Application\Common\PackagesLoader;
    use ObjectivePHP\Application\Pattern\Rta\Router\RtARouter;
    use ObjectivePHP\Application\Workflow\Event\WorkflowEvent;

    class RtaCallbacksAggregate
    {

        public function __invoke(WorkflowEvent $event)
        {
            $application = $event->getApplication();
            $workflow = $application->getWorkflow();

            $workflow->bind('packages.pre', PackagesLoader::class);
            $workflow->bind('packages.post', RequestFactory::class);
            $workflow->bind('run.route', RtaRouter::class);
            $workflow->bind('run.render', PhtmlRenderer::class);

        }

    }