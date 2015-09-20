<?php

    namespace ObjectivePHP\Application\Task\Common;


    use ObjectivePHP\Application\Workflow\Event\WorkflowEvent;
    use Zend\Diactoros\Response\SapiEmitter;

    /**
     * Class ResponseSender
     *
     * @package ObjectivePHP\Application\Task\Common
     */
    class SendResponse
    {
        /**
         * @param WorkflowEvent $event
         */
        public function __invoke(WorkflowEvent $event)
        {
            $response = $event->getApplication()->getResponse();
            $emitter = new SapiEmitter();

            $emitter->emit($response);
        }

    }