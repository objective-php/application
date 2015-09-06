<?php

    namespace ObjectivePHP\Application\Action\Parameter;
    
    
    use ObjectivePHP\Primitives\String\String;

    class StringParameter extends AbstractParameterProcessor
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