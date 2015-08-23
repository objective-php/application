<?php

    namespace ObjectivePHP\Application\Task;
    
    
    use ObjectivePHP\Application\Workflow\Event\WorkflowEvent;
    use ObjectivePHP\Events\EventsHandler;

    class RtaCallbacksAggregate
    {

        public function __invoke(WorkflowEvent $event)
        {
            $application = $event->getApplication();
            $workflow = $application->getWorkflow();

            $workflow->bind('bootstrap', Common\WrapRequest::class);
            $workflow->bind('packages.pre', Common\BootstrapPackages::class);
            $workflow->bind('route.resolve', ['action-resolver' => Rta\RouteRequestToAction::class]);

            // capture output
            $workflow->bind('bootstrap', function() { ob_start(); });

            // handle rendering
            $workflow->bind('response.generate', ['view-resolver' => Rta\ResolveView::class]);
            $workflow->bind('response.generate', ['view-renderer' => Common\RenderView::class]);
            $workflow->bind('response.generate', ['layout-renderer' => Common\RenderLayout::class]);

        }

    }