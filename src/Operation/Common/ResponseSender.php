<?php

    namespace ObjectivePHP\Application\Operation\Common;


    use ObjectivePHP\Application\ApplicationInterface;
    use ObjectivePHP\Application\Workflow\Event\WorkflowEvent;
    use Zend\Diactoros\Response\SapiEmitter;

    /**
     * Class ResponseSender
     *
     * @package ObjectivePHP\Application\Task\Common
     */
    class ResponseSender
    {
        /**
         * @param WorkflowEvent $event
         */
        public function __invoke(ApplicationInterface $app)
        {
            $response = $app->getResponse();
            $emitter = new SapiEmitter();

            $emitter->emit($response);
        }

    }