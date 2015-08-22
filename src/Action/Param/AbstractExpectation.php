<?php
    namespace ObjectivePHP\Application\Action\Param;
    
    use ObjectivePHP\Primitives\String\String;

    abstract class AbstractExpectation implements ExpectationInterface
    {
        /**
         * @var mixed   Parameter name or position
         */
        protected $reference;

        /**
         * @var string Alias for positioned parameters (or not)
         */
        protected $alias;

        /**
         * @var bool
         */
        protected $mandatory = false;

        protected $message;

        /**
         * @param            $reference
         * @param bool|false $mandatory
         * @param null       $message
         */
        public function __construct($reference, $mandatory = false, $message = null)
        {

            if(is_array($reference))
            {
                list($reference, $alias) = each($reference);
            }
            else
            {
                $alias = null;
            }

            $this->setReference($reference);
            $this->setMandatory($mandatory);
            $this->setAlias($alias);

            // set default message
            if($mandatory && is_null($message))
            {
                $this->setMessage(new String('Missing mandatory parameter ":param"'));
            }
        }

        /**
         * @return string|int
         */
        public function getReference()
        {
            return $this->reference;
        }

        /**
         * @param mixed $reference
         *
         * @return $this
         */
        public function setReference($reference)
        {
            $this->reference = $reference;

            return $this;
        }

        /**
         * @return bool
         */
        public function isMandatory()
        {
            return $this->mandatory;
        }

        /**
         * @param bool $switch
         */
        public function setMandatory($switch = true)
        {
            $this->mandatory = (bool) $switch;

            return $this;
        }

        /**
         * @return string
         */
        public function getMessage()
        {
            return $this->message;
        }

        /**
         * @param string $message
         *
         * @return $this
         */
        public function setMessage($message)
        {
            $this->message = String::cast($message)->setVariable('param', $this->getReference());

            return $this;
        }

        /**
         * @return string
         */
        public function getAlias()
        {
            return $this->alias;
        }

        /**
         * @param string $alias
         *
         * @return $this
         */
        public function setAlias($alias)
        {
            $this->alias = $alias;

            return $this;
        }

    }