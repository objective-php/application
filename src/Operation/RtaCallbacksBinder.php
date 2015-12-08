<?php

    namespace ObjectivePHP\Application\Operation;
    
    
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
            $workflow->bind('bootstrap', new AliasedCallback('request-wrapper', Common\RequestWrapper::class));
            $workflow->bind('bootstrap', new AliasedCallback('response-initializer', Common\ResponseInitializer::class));

            // load initial services
            $workflow->bind('bootstrap', new AliasedCallback('initial-services-loader', Common\ServiceLoader::class));

            // bind packages to packages.load
            $workflow->bind('packages.pre', new AliasedCallback('packages-bootstrapper', Common\PackageLoader::class));

            // load packages services
            $workflow->bind('packages.post', new AliasedCallback('services-loader', Common\ServiceLoader::class));

            // define what action to execute
            $workflow->bind('route.resolve', new AliasedCallback('action-resolver', Rta\ActionRunner::class));

            // handle rendering
            $workflow->bind('response.generate', new AliasedCallback('view-resolver', Rta\ViewResolver::class));
            $workflow->bind('response.generate', new AliasedCallback('view-renderer', Common\ViewRenderer::class));
            $workflow->bind('response.generate', new AliasedCallback('layout-renderer', Common\LayoutRenderer::class));

            // return response
            $workflow->bind('response.send', new AliasedCallback('response-emitter', Common\ResponseSender::class));

            // exception handling
            $application->getEventsHandler()->bind('workflow.exception', new AliasedCallback('exception-reporter', Common\DisplayException::class));


        }

    }