<?php

    namespace ObjectivePHP\Application\View\Helper;
    
    
    class Date
    {
        /**
         * @var string DateTime::format() compatible date format
         */
        protected static $defaultFormat = 'Y-m-d';


        public static function format(\DateTime $dateTime, $format = null)
        {
            $format = $format ?: self::getDefaultFormat();

            return $dateTime->format($format);
        }

        /**
         * @return string
         */
        public static function getDefaultFormat()
        {
            return self::$defaultFormat;
        }

        /**
         * @param string $defaultFormat
         */
        public static function setDefaultFormat($defaultFormat)
        {
            self::$defaultFormat = $defaultFormat;
        }



    }