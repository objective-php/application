<?php

    namespace ObjectivePHP\Application\Workflow;
    
    
    use ObjectivePHP\Application\ApplicationInterface;
    use ObjectivePHP\Application\Workflow\Step\StepInterface;
    use ObjectivePHP\Events\EventsHandler;

    /**
     * Interface WorkflowInterface
     *
     * @package ObjectivePHP\Application\Workflow
     */
    interface WorkflowInterface extends StepInterface
    {
        /**
         * @param ...$steps
         *
         * @return mixed
         */
        public function addStep(...$steps);

        /**
         * @return mixed
         */
        public function getSteps();

        /**
         * @param $step
         *
         * @return mixed
         */
        public function getStep($step);

        /**
         * @return mixed
         */
        public function doesAutoTriggerPrePostEvents();

        /**
         * @return mixed
         */
        public function run();

        /**
         * @param WorkflowInterface $workflow
         *
         * @return mixed
         */
        public function setParent(WorkflowInterface $workflow);

        /**
         * @param EventsHandler $eventsHandler
         *
         * @return mixed
         */
        public function setEventsHandler(EventsHandler $eventsHandler);

        /**
         * @return WorkflowInterface
         */
        public function getParent();

        /**
         * @param ApplicationInterface $application
         *
         * @return mixed
         */
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

        /**
         * Interrupt workflow execution
         *
         * @return mixed
         */
        public function halt();

        /**
         * Return the top parent
         *
         * @return WorkflowInterface
         */
        public function getRoot();
    }