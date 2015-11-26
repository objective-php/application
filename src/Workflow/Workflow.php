<?php

    namespace ObjectivePHP\Application\Workflow;

    use ObjectivePHP\Application\Workflow\Event\WorkflowEvent;
    use ObjectivePHP\Html\Tag\Tag;
    use ObjectivePHP\Primitives\String\Str;


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
                $currentEvent = $this->getRoot()->getEvents()->last();
                if($currentEvent)
                {
                    $currentEvent->halt();
                    // halt complete workflow
                    $this->halt();
                }


                // trigger dedicated event
                $event = (new WorkflowEvent())->setApplication($this->getRoot()->getApplication());
                $this->getEventsHandler()->trigger('workflow.exception', $this, ['exception' => $e], $event);
            }

        }

    }