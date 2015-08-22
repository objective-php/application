<?php

    namespace ObjectivePHP\Application\Action\Param;
    
    
    use ObjectivePHP\Primitives\String\String;

    class StringParameter extends AbstractExpectation
    {

        /**
         * @param $value
         *
         * @return int
         */
        public function process($value)
        {
            return String::cast($value);
        }

    }