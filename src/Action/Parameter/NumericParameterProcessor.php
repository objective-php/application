<?php

    namespace ObjectivePHP\Application\Action\Parameter;

    use ObjectivePHP\Application\Exception;

    class NumericParameterProcessor extends AbstractParameterProcessor
    {

        const NOT_A_NUMBER = 'NaN';

        /**
         * Constructor
         *
         * @param string     $reference Parameter reference
         * @param int|string $mapping   Query parameter name or position. If none provided, $reference is used as
         *                              mapping.
         */
        public function __construct($reference, $mapping = null)
        {
            parent::__construct($reference, $mapping);

            // set specific messages
            $this->setMessage(self::NOT_A_NUMBER, 'The parameter value is not numeric');
        }

        /**
         * @param $value
         *
         * @return int
         */
        public function process($value)
        {
            if($this->isMandatory() && is_null($value))
            {
                throw new Exception($this->getMessage(AbstractParameterProcessor::IS_MISSING));
            }


            if(!is_numeric($value))
            {
                throw new Exception($this->getMessage(self::NOT_A_NUMBER));
            }



            return $value / 1;
        }

    }