<?php

    namespace ObjectivePHP\Application\Task;
    
    
    use ObjectivePHP\Application\Workflow\Event\WorkflowEvent;
    use ObjectivePHP\Events\Callback\AliasedCallback;

    /**
     * Class RtaCallbacksBinder
     *
     * @package ObjectivePHP\Application\Task
     */
    class RtaCallbacksBinder
    {

        /**
         * @param WorkflowEvent $event
         *
         * @throws \ObjectivePHP\Events\Exception
         */
        public function __invoke(WorkflowEvent $event)
        {
            $application = $event->getApplication();

            // Main workflow
            $workflow = $application->getWorkflow();

            // bootstrap application
            $workflow->bind('bootstrap', new AliasedCallback('request-wrapper', Common\WrapRequest::class));
            $workflow->bind('bootstrap', new AliasedCallback('response-initializer', Common\InitializeResponse::class));

            // load initial services
            $workflow->bind('bootstrap', new AliasedCallback('initial-services-loader', Common\LoadServices::class));

            // bind packages to packages.load
            $workflow->bind('packages.pre', new AliasedCallback('packages-bootstrapper', Common\BootstrapPackages::class));

            // load packages services
            $workflow->bind('packages.post', new AliasedCallback('services-loader', Common\LoadServices::class));

            // define what action to execute
            $workflow->bind('route.resolve', new AliasedCallback('action-resolver', Rta\RouteRequestToAction::class));

            // handle rendering
            $workflow->bind('response.generate', new AliasedCallback('view-resolver', Rta\ResolveView::class));
            $workflow->bind('response.generate', new AliasedCallback('view-renderer', Common\RenderView::class));
            $workflow->bind('response.generate', new AliasedCallback('layout-renderer', Common\RenderLayout::class));

            // return response
            $workflow->bind('response.send', new AliasedCallback('response-emitter', Common\SendResponse::class));

            // exception handling
            $application->getEventsHandler()->bind('workflow.exception', new AliasedCallback('exception-reporter', Common\DisplayException::class));


        }

    }