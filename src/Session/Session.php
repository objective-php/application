<?php


    namespace ObjectivePHP\Application\Session;

    use ObjectivePHP\Application\Exception;

    /**
     * Class Session
     */
    class Session
    {

        CONST SESSION_MODE_NATIVE = 'native';

        CONST SESSION_MODE_MOCK = 'mock';

        static protected $defaultMode = self::SESSION_MODE_NATIVE;


        /**
         * @var array Either a reference to $_SESSION or a local array, depending on mode
         */
        static protected $data;

        /**
         * Session constructor.
         *
         * @param string $namespace
         */
        public function __construct($mode = null)
        {

            if(is_null($mode)) $mode = self::$defaultMode;

            if($mode == self::SESSION_MODE_NATIVE)
            {

                $status = session_status();

                if ($status == PHP_SESSION_DISABLED)
                {
                    throw new Exception('Session are disabled on server');
                }

                if ($status == PHP_SESSION_NONE)
                {
                    session_start();
                }

                self::$data = &$_SESSION;
            }
            elseif($mode == self::SESSION_MODE_MOCK)
            {
                self::$data = [];
            }
            else
            {
                throw new Exception(sprintf('Unkown Session mode "%s"', $mode));
            }

        }


        /**
         * @param $key
         * @param $value
         *
         * @return $this
         * @throws \ObjectivePHP\Primitives\Exception
         */
        public function set($key, $value)
        {
            $this->data[$key] = $value;

            return $this;
        }

        /**
         * @param      $key
         * @param null $default
         *
         * @return mixed|null
         * @throws \ObjectivePHP\Primitives\Exception
         */
        public function get($key, $default = null)
        {
            return isset($this->data[$key]) ? $this->data[$key] : $default;
        }

        /**
         * Get the whole session array
         *
         * @return array
         */
        public function getData()
        {
            return self::$data;
        }

        /**
         * @return string
         */
        public static function getDefaultMode()
        {
            return self::$defaultMode;
        }

        /**
         * @param string $defaultMode
         */
        public static function setDefaultMode($defaultMode)
        {
            self::$defaultMode = $defaultMode;
        }


    }