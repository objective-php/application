<?php
    namespace ObjectivePHP\Application\Action\Parameter;
    
    use ObjectivePHP\Primitives\Collection\Collection;
    use ObjectivePHP\Primitives\String\Str;
    use ObjectivePHP\Application\ApplicationInterface;

    abstract class AbstractParameterProcessor implements ParameterProcessorInterface
    {
        const IS_MISSING = 'action-parameter.is-missing';

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
         * @var Callable
         */
        protected $validator;

        /**
         * Error message templates
         *
         * @var Collection
         */
        protected $messages = [self::IS_MISSING => 'Missing mandatory parameter ":reference"'];

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
        public function getMessage($code = self::IS_MISSING, $values = [])
        {
            $message = Str::cast($this->getMessages()->get($code));

            $paramName = $this->getReference();

            if (($mapping = $this->getQueryParameterMapping()) !== null)
            {
                $paramName .= ' (' . (is_int($mapping) ? '#' : '') . $mapping . ' in query)';
            }

            $message->setVariable('reference', $paramName);

            foreach($values as $placeHolder => $value)
            {
                $message->setVariable($placeHolder, $value);
            }

            return $message;
        }

        /**
         * @param string $message
         *
         * @return $this
         */
        public function setMessage($code, $message)
        {
            $this->messages[$code] = Str::cast($message);

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

        /**
         * @return Callable
         */
        public function getValidator()
        {
            return $this->validator;
        }

        /**
         * @param Callable $validator
         *
         * @return $this
         */
        public function setValidator(callable $validator)
        {
            $this->validator = $validator;

            return $this;
        }



    }