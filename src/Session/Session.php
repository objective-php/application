<?php


    namespace ObjectivePHP\Application\Session;

    use ObjectivePHP\Application\Exception;
    use ObjectivePHP\Matcher\Matcher;
    use ObjectivePHP\Primitives\Collection\Collection;

    /**
     * Class Session
     */
    class Session
    {

        CONST SESSION_MODE_NATIVE = 'native';

        CONST SESSION_MODE_MOCK = 'mock';

        static protected $defaultMode = self::SESSION_MODE_NATIVE;

        /**
         * @var Matcher
         */
        protected $matcher;

        /**
         * @var array Either a reference to $_SESSION or a local array, depending on mode
         */
        static protected $data;

        /**
         * @var string
         */
        protected $namespace;

        /**
         * Session constructor.
         *
         * @param string $namespace
         */
        public function __construct($namespace = null, $mode = null)
        {

            if(is_null($mode)) $mode = self::$defaultMode;

            $this->setNamespace((string) $namespace);

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
         * @return string
         */
        public function getNamespace()
        {
            return $this->namespace;
        }

        /**
         * @param string $namespace
         *
         * @return $this
         */
        public function setNamespace($namespace)
        {
            $this->namespace = (string) $namespace;

            return $this;
        }

        /**
         * @return $this
         */
        public function resetNamespace()
        {
            $this->namespace = null;

            return $this;
        }

        /**
         * @param $key
         * @param $value
         *
         * @return $this
         * @throws \ObjectivePHP\Primitives\Exception
         */
        public function set($reference, $value)
        {
            $reference = $this->computeKeyFQN($reference);
            self::$data[$reference] = $value;

            return $this;
        }

        /**
         * @param      $reference
         * @param null $default
         *
         * @return mixed|null
         * @throws \ObjectivePHP\Primitives\Exception
         */
        public function get($reference, $default = null)
        {
            $reference = $this->computeKeyFQN($reference);

            // first look for exact match
            if(isset(self::$data[$reference]))
            {
                return self::$data[$reference];
            }

            // otherwise use a matcher to return a
            // collection of matching entries
            $matcher = $this->getMatcher();
            $matches = new Collection();

            Collection::cast(self::$data)->each(function(&$value, $key) use($matcher, $reference, $matches)
            {
                if($matcher->match($reference, $key))
                {
                    $matches[$key] = $value;
                }
            });

                if(!$matches->isEmpty()) return $matches;

            return  $default;
        }

        /**
         * @param $key
         *
         * @return $this
         */
        public function remove($reference)
        {
            $reference = $this->computeKeyFQN($reference);
            $matcher = $this->getMatcher();

            Collection::cast(self::$data)->each(function (&$value, $key) use ($matcher, $reference)
            {
                if($matcher->match($reference, $key))
                {
                    unset(self::$data[$key]);
                }
            });
                return $this;
        }

        /**
         * @return $this
         */
        public function clear()
        {
            if(!$this->namespace) self::$data = [];
            else $this->remove('*');

            return $this;
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

        /**
         * @return Matcher
         */
        public function getMatcher()
        {

            if(is_null($this->matcher))
            {
                $this->matcher = new Matcher();
            }

            return $this->matcher;
        }

        /**
         * @param Matcher $matcher
         *
         * @return $this
         */
        public function setMatcher(Matcher $matcher)
        {
            $this->matcher = $matcher;

            return $this;
        }

        /**
         * @param $key
         *
         * @return string
         */
        protected function computeKeyFQN($key)
        {
            return $this->namespace ? $this->namespace . $this->getMatcher()->getSeparator() . $key : $key;
        }

    }