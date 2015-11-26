<?php

    namespace ObjectivePHP\Application\View\Helper;
    
    
    use ObjectivePHP\Primitives\Collection\Collection;
    use ObjectivePHP\Primitives\String\Str;

    class Vars
    {

        protected static $capturing = false;

        protected static $variables;

        /**
         * @param      $reference
         * @param null $default
         *
         * @return mixed
         */
        public static function get($reference, $default = null)
        {
            return @self::$variables[$reference] ?: $default;
        }

        /**
         * @param $reference
         * @param $value
         */
        public static function set($reference, $value = null)
        {
            if(is_null($value) && self::$capturing)
            {
                $value = ob_get_clean();
                self::$capturing = false;
            }

            self::$variables[$reference] = $value;
        }

        /**
         * @param $reference
         *
         * @return \ObjectivePHP\Primitives\String\Str
         */
        public static function string($reference)
        {
            return Str::cast(self::get($reference));
        }

        /**
         * @param $reference
         *
         * @return \ObjectivePHP\Primitives\Collection\Collection
         */
        public static function collection($reference)
        {
            return Collection::cast(self::get($reference));
        }

        /**
         * Start capturing output
         */
        public static function capture()
        {
            ob_start();
            self::$capturing = true;
        }

    }