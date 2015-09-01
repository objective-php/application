<?php

    namespace ObjectivePHP\Application\Action\Param;
    
    
    use ObjectivePHP\Application\ApplicationInterface;

    interface ExpectationInterface
    {

        public function setApplication(ApplicationInterface $application);

        public function getApplication();

        public function getReference();

        public function isMandatory();

        public function process($value);

        public function getMessage();
    }