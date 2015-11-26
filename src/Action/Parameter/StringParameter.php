<?php

    namespace ObjectivePHP\Application\Action\Parameter;
    
    
    use ObjectivePHP\Application\Exception;
    use ObjectivePHP\Primitives\String\Str;

    class StringParameter extends AbstractParameterProcessor
    {

        /**
         * @param $value
         *
         * @return int
         */
        public function process($value)
        {
            if ($this->isMandatory() && is_null($value))
            {
                throw new Exception($this->getMessage(AbstractParameterProcessor::IS_MISSING));
            }

            return Str::cast($value);
        }

    }