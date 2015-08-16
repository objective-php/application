<?php

    namespace ObjectivePHP\Application\Workflow\Step;
    
    
    interface StepInterface
    {
        /**
         * @return string
         */
        public function getName();

        /**
         * @return boolean
         */
        public function doesSharePreviousEvent();
    }