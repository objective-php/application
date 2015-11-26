<?php

    namespace ObjectivePHP\Application\View\Helper;

    /**
     * Class Currency
     *
     * Format a number as a displayable currency. Currency symbol and position
     * are configurable, whereas number format itself depends on locale.
     *
     * @package ObjectivePHP\Application\View\Helper
     */
    class Currency
    {
        const APPEND  = 'append';
        const PREPEND = 'prepend';

        protected static $defaultSymbol          = '$';

        protected static $defaultSymbolPlacement = self::PREPEND;

        protected static $defaultSpacer          = '';

        /**
         * @param string|number $amount    Amount to display. If a string is passed, it is displayed as is,
         *                                 otherwise formatted using number_format()
         * @param null          $symbol    Currency symbol to display instead of default one
         * @param null          $placement Currency symbol placement to use instead of default one
         *
         * @return Str
         */
        public static function format($amount, $symbol = null, $symbolPlacement = null, $spacer = null)
        {
            $amount          = is_string($amount) ?: number_format($amount);
            $symbol          = $symbol ?: self::getDefaultSymbol();
            $symbolPlacement = $symbolPlacement ?: self::getDefaultSymbolPlacement();
            $spacer = $spacer ?: self::getDefaultSpacer();

            if ($symbolPlacement == self::PREPEND)
            {
                $currencyString = $symbol . $spacer . $amount;
            }
            else
            {
                $currencyString = $amount . $spacer . $symbol;
            }

            return $currencyString;
        }

        /**
         * @return string
         */
        public static function getDefaultSymbol()
        {
            return self::$defaultSymbol;
        }

        /**
         * @param string $defaultSymbol
         */
        public static function setDefaultSymbol($defaultSymbol)
        {
            self::$defaultSymbol = $defaultSymbol;
        }

        /**
         * @return string
         */
        public static function getDefaultSymbolPlacement()
        {
            return self::$defaultSymbolPlacement;
        }

        /**
         * @param string $defaultSymbolPlacement
         */
        public static function setDefaultSymbolPlacement($defaultSymbolPlacement)
        {
            self::$defaultSymbolPlacement = $defaultSymbolPlacement;
        }

        /**
         * @return string
         */
        public static function getDefaultSpacer()
        {
            return self::$defaultSpacer;
        }

        /**
         * @param string $defaultSpacer
         */
        public static function setDefaultSpacer($defaultSpacer)
        {
            self::$defaultSpacer = $defaultSpacer;
        }

    }