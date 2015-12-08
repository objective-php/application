<?php

    namespace ObjectivePHP\Application\Operation\Common;
    
    
    use ObjectivePHP\Application\Workflow\Event\WorkflowEvent;
    use ObjectivePHP\Html\Tag\Tag;
    use ObjectivePHP\Primitives\String\Str;
    use Zend\Diactoros\Response\SapiEmitter;

    /**
     * Class DisplayException
     *
     * @package ObjectivePHP\Application\Task\Common
     */
    class DisplayException
    {
        /**
         * @param WorkflowEvent $event
         *
         * @throws \ObjectivePHP\Primitives\Exception
         */
        public function __invoke(WorkflowEvent $event)
        {
            $body = $event->getApplication()->getResponse()->getBody();

            $exception = $event->getContext()['exception'];
            $workflow = $event->getWorkflow();

            $div = Tag::div(Tag::h1('An exception has been thrown'), 'errors');


            $div->append(Tag::h2('Event'), Tag::pre($event->getPrevious()->getName()));
            $div->append(Tag::h2('Message'), Tag::pre($exception->getMessage()));
            $div->append(Tag::h2('File'), Tag::pre($exception->getFile())->append(':', $exception->getLine())->setSeparator(''));

            // shorten Trace
            $trace = Str::cast($exception->getTraceAsString())->replace(getcwd() . '/', '');

            $div->append(Tag::h2('Trace'), Tag::pre($trace));

            $body->write((string) $div);

            if($previousException = $exception->getPrevious())
            {
                $event->getContext()->set('exception', $previousException);
                $this($event, false);
            }

            // manually emit response
            (new SapiEmitter())->emit($event->getApplication()->getResponse()->withStatus(500));

        }
    }