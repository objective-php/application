<?php

    namespace ObjectivePHP\Application\Workflow;

    use ObjectivePHP\Application\Workflow\Event\WorkflowEvent;
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
                $event = (new WorkflowEvent())->setApplication($this->getApplication());
                $this->getEventsHandler()->trigger('workflow.error', $this, ['exception' => $e], $event);

            }

        }

    }