<?php

    namespace ObjectivePHP\Application\View\Helper;
    
    
    use ObjectivePHP\Primitives\Collection\Collection;
    use ObjectivePHP\Primitives\String\String;

    class Vars
    {

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
        public static function set($reference, $value)
        {
            self::$variables[$reference] = $value;
        }

        /**
         * @param $reference
         *
         * @return \ObjectivePHP\Primitives\String\String
         */
        public static function string($reference)
        {
            return String::cast((self::get($reference)));
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
    }