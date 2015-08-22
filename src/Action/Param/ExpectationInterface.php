<?php

    namespace ObjectivePHP\Application\Action\Param;
    
    
    interface ExpectationInterface
    {
        public function getReference();

        public function isMandatory();

        public function process($value);

        public function getMessage();
    }