<?php

    namespace ObjectivePHP\Application\Common;
    
    
    use ObjectivePHP\Application\Workflow\Event\WorkflowEvent;
    use ObjectivePHP\Message\Request\HttpRequest;

    class RequestFactory
    {
        public function __invoke(WorkflowEvent $event)
        {
                $request = new HttpRequest($_SERVER['REQUEST_URI']);

                $event->getApplication()->setRequest($request);
        }
    }