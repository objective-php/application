<?php

    namespace ObjectivePHP\Application\Task\Common;
    
    
    use ObjectivePHP\Application\Workflow\Event\WorkflowEvent;
    use ObjectivePHP\Html\Tag\Tag;
    use ObjectivePHP\Primitives\String\String;

    class DisplayException
    {
        public function __invoke(WorkflowEvent $event)
        {

            $exception = $event->getContext()['exception'];
            $workflow = $event->getWorkflow();


            $div = Tag::div(Tag::h1('An exception has been thrown'), 'errors');


            $div->append(Tag::h2('Event'), Tag::pre($workflow->getEvents()->last()->getName()));
            $div->append(Tag::h2('Message'), Tag::pre($exception->getMessage()));
            $div->append(Tag::h2('File'), Tag::pre($exception->getFile())->append(':', $exception->getLine())->setSeparator(''));

            // shorten Trace
            $trace = String::cast($exception->getTraceAsString())->replace(getcwd(), '');

            $div->append(Tag::h2('Trace'), Tag::pre($trace));

            echo $div;

            flush();

        }
    }