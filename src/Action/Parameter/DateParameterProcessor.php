<?php
    /**
     * Created by PhpStorm.
     * User: gauthier
     * Date: 28/11/2015
     * Time: 12:19
     */
    
    namespace ObjectivePHP\Application\Action\Parameter;
    
    
    use ObjectivePHP\Application\Exception;

    class DateParameterProcessor extends AbstractParameterProcessor
    {

        const INVALID_FORMAT = 'invalid_format';


        protected $format = 'Y-m-d';

        public function process($value)
        {
            $this->setMessage(self::INVALID_FORMAT, 'The parameter value does not match expected date format (":format")');

            $date = \DateTime::createFromFormat($this->getFormat(), $value);


            if($date === false)
            {
                throw new Exception($this->getMessage(self::INVALID_FORMAT, ['format' => $this->getFormat()]));
            }

            return $date;

        }



        /**
         * @return string
         */
        public function getFormat() : string
        {
            return $this->format;
        }

        /**
         * @param string $format
         *
         * @return $this
         */
        public function setFormat(string $format)
        {
            $this->format = $format;

            return $this;
        }


    }