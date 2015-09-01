<?php

    namespace ObjectivePHP\Application\Workflow;

    use ObjectivePHP\Html\Tag\Tag;
    use ObjectivePHP\Primitives\String\String;


    /**
     * Class Workflow
     *
     * Default work class used for sub workflow
     *
     * @package ObjectivePHP\Application\Workflow
     */
    class Workflow extends AbstractWorkflow
    {
        /**
         * Actually runs the workflow
         */
        public function run()
        {
            try
            {
                parent::run();
            } catch(\Exception $e)
            {
                // clear buffered output
                ob_get_clean();

                // halt current event
                $this->getEvents()->last()->halt();

                // halt complete workflow
                $this->halt();

                // display information about Exception
                $this->displayException($e);
            }

        }


        protected function displayException(\Exception $exception)
        {
            $div = Tag::div(Tag::h1('An exception has been thrown'), 'errors');


            $div->append(Tag::h2('Event'), Tag::pre($this->getEvents()->last()->getNAme()));
            $div->append(Tag::h2('Message'), Tag::pre($exception->getMessage()));
            $div->append(Tag::h2('File'), Tag::pre($exception->getFile())->append(':', $exception->getLine())->setSeparator(''));

            // shorten Trace
            $trace = String::cast($exception->getTraceAsString())->replace(getcwd(), '');

            $div->append(Tag::h2('Trace'), Tag::pre($trace)->append(':', $exception->getLine()));

            echo $div;

            flush();

        }

    }