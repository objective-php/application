<?php

    namespace ObjectivePHP\Application\Action\Parameter;
    
    
    use ObjectivePHP\Primitives\Numeric\Numeric;

    class NumericParameter extends AbstractParameterProcessor
    {

        /**
         * @param $value
         *
         * @return int
         */
        public function process($value)
        {
            return Numeric::cast($value);
        }

    }