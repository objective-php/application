<?php

    namespace ObjectivePHP\Application\Action\Parameter;
    
    
    use ObjectivePHP\Application\Exception;
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
            if($this->isMandatory() && is_null($value))
            {
                throw new Exception($this->getMessage(ActionParameter::IS_MISSING));
            }

            return Numeric::cast($value);
        }

    }