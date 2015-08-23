<?php

    namespace ObjectivePHP\Application\Workflow;
    
    
    use ObjectivePHP\Application\ApplicationInterface;
    use ObjectivePHP\Events\EventsHandler;
    use ObjectivePHP\Application\Workflow\Step\StepInterface;

    interface WorkflowInterface extends StepInterface
    {
        public function addStep(...$steps);

        public function getSteps();

        public function getStep($step);

        public function doesAutoTriggerPrePostEvents();

        public function run();

        public function setParent(WorkflowInterface $workflow);

        public function setEventsHandler(EventsHandler $eventsHandler);

        /**
         * @return WorkflowInterface
         */
        public function getParent();

        public function setApplication(ApplicationInterface $application);

        /**
         * @return ApplicationInterface
         */
        public function getApplication();

        /**
         * Bind a callback to workflow event
         *
         * @return $this
         */
        public function bind($eventName, $callback, $mode = EventsHandler::BINDING_MODE_LAST);


        /**
         * Unbind all callbacks from a workflow event
         *
         * @param $eventName
         *
         * @return $this
         */
        public function unbind($eventName);

    }