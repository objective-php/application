<?php

    namespace ObjectivePHP\Application\Task\Common;
    
    
    use ObjectivePHP\Application\Workflow\Event\WorkflowEvent;
    use ObjectivePHP\Message\Request\HttpRequest;

    class WrapRequest
    {
        public function __invoke(WorkflowEvent $event)
        {
            if(isset($_SERVER['REQUEST_URI']))
            {
                $request = new HttpRequest($_SERVER['REQUEST_URI']);

                $event->getApplication()->setRequest($request);
            }
            else
            {
                // TODO handle cli requests
            }
        }
    }