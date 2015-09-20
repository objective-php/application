<?php
    namespace ObjectivePHP\Application\Action\Parameter;
    
    use ObjectivePHP\Primitives\Collection\Collection;
    use ObjectivePHP\Primitives\String\String;
    use ObjectivePHP\Application\ApplicationInterface;

    abstract class AbstractParameterProcessor implements ParameterProcessorInterface
    {
        /**
         * @var mixed   Parameter name or position
         */
        protected $reference;

        /**
         * @var string Alias for positioned parameters (or not)
         */
        protected $queryParameterMapping;

        /**
         * @var bool
         */
        protected $mandatory = false;

        /**
         * @var ApplicationInterface
         */
        protected $application;

        /**
         * Error message templates
         *
         * @var Collection
         */
        protected $messages = [ActionParameter::IS_MISSING => 'Missing mandatory parameter ":reference"'];

        /**
         * Constructor
         *
         * @param string     $reference Parameter reference
         * @param int|string $mapping   Query parameter name or position. If none provided, $reference is used as mapping.
         */
        public function __construct($reference, $mapping = null)
        {

            $this->setReference($reference);

            // if no mapping is defined, use $reference as mapping
            if(is_null($mapping))
            {
                $mapping = $reference;
            }

            $this->setQueryParameterMapping($mapping);

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
         * Return all defined messages
         *
         * @return Collection
         */
        public function getMessages()
        {
            return Collection::cast($this->messages);
        }

        /**
         * @return string
         */
        public function getMessage($code = ActionParameter::IS_MISSING)
        {
            return $this->getMessages()->get($code);
        }

        /**
         * @param string $message
         *
         * @return $this
         */
        public function setMessage($code, $message)
        {
            $paramName = $this->getQueryParameterMapping() ? $this->getQueryParameterMapping() . ' (#' . $this->getReference() . ')' : $this->getReference();
            $this->messages[$code] = String::cast($message)->setVariable('param', $paramName);

            return $this;
        }

        /**
         * @return string
         */
        public function getQueryParameterMapping()
        {
            return $this->queryParameterMapping;
        }

        /**
         * @param string $queryParameterMapping
         *
         * @return $this
         */
        public function setQueryParameterMapping($queryParameterMapping)
        {
            $this->queryParameterMapping = $queryParameterMapping;

            return $this;
        }

        /**
         * @return ApplicationInterface
         */
        public function getApplication()
        {
            return $this->application;
        }

        /**
         * @param ApplicationInterface $application
         *
         * @return $this
         */
        public function setApplication(ApplicationInterface $application)
        {
            $this->application = $application;

            return $this;
        }

    }