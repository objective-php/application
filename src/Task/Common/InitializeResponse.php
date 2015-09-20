<?php
    /**
     * Created by PhpStorm.
     * User: gauthier
     * Date: 07/09/15
     * Time: 16:41
     */
    
    namespace ObjectivePHP\Application\Task\Common;

    use ObjectivePHP\Application\Workflow\Event\WorkflowEvent;
    use ObjectivePHP\Message\Response\HttpResponse;

    /**
     * Class InitializeResponse
     *
     * @package ObjectivePHP\Application\Task\Common
     */
    class InitializeResponse
    {
        /**
         * Instantiate Response
         *
         * @param WorkflowEvent $event
         */
        public function __invoke(WorkflowEvent $event)
        {
            // TODO handle CLI repsonse
            $event->getApplication()->setResponse(new HttpResponse());
        }
    }