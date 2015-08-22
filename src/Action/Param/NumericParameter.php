<?php

    namespace ObjectivePHP\Application\Action\Param;
    
    
    use ObjectivePHP\Primitives\Numeric\Numeric;

    class NumericParameter extends AbstractExpectation
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