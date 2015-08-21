<?php
    
    namespace ObjectivePHP\Application\Workflow\Event;

    use ObjectivePHP\Application\ApplicationInterface;
    use ObjectivePHP\Application\Workflow\WorkflowInterface;
    use ObjectivePHP\Events\Event;


    class WorkflowEvent extends Event
    {
        /**
         * @var ApplicationInterface
         */
        protected $application;

        /**
         * @return ApplicationInterface
         */
        public function getApplication()
        {
            return $this->application;
        }

        /**
         * @param ApplicationInterface $application
         *
         * @return $this
         */
        public function setApplication(ApplicationInterface $application)
        {
            $this->application = $application;

            return $this;
        }

        /**
         * @codeAssist
         * @return WorkflowInterface
         */
        public function getWorkflow()
        {
            return $this->getOrigin();
        }


    }