<?php
    namespace ObjectivePHP\Application\Action\Parameter;
    
    use ObjectivePHP\Application\ApplicationAwareInterface;
    use ObjectivePHP\Application\Exception;
    use ObjectivePHP\DataProcessor\DataProcessorInterface;
    use ObjectivePHP\Primitives\Collection\Collection;
    use ObjectivePHP\Primitives\String\Str;
    use ObjectivePHP\Application\ApplicationInterface;

    class ParameterProcessor implements ParameterProcessorInterface
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
        protected $messages = [self::IS_MISSING => 'Missing mandatory parameter ":param"'];


        /**
         * @var DataProcessorInterface
         */
        protected $dataProcessor;

        /**
         * Constructor
         *
         * @param DataProcessorInterface $dataProcessor
         * @param string $reference Parameter reference
         * @param int|string $mapping Query parameter name or position. If none provided, $reference is used as mapping.
         */
        public function __construct(DataProcessorInterface $dataProcessor, $reference, $mapping = null)
        {

            $this->setDataProcessor($dataProcessor);

            $this->setReference($reference);

            // if no mapping is defined, use $reference as mapping
            if (is_null($mapping))
            {
                $mapping = $reference;
            }

            $this->setQueryParameterMapping($mapping);

        }

        /**
         * Process a value
         *
         * The processed value will be stored as parameter value
         *
         * @param mixed $value
         * @return mixed
         * @throws Exception
         */
        public function process($value)
        {

            if ($this->isMandatory() && !$value)
            {
                throw new Exception($this->getMessage(self::IS_MISSING, ['param', $this->reference]));
            }

            $dataProcessor = $this->getDataProcessor();

            if ($dataProcessor instanceof ApplicationAwareInterface)
            {
                $dataProcessor->setApplication($this->getApplication());
            }

            $processedValue = $dataProcessor->process($value);

            if($this->isMandatory() && is_null($processedValue))
            {
                throw new Exception($this->getMessage(self::IS_MISSING, ['param', $this->reference]));
            }

            return $processedValue;
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
         * @return $this|mixed
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
                $paramName .= ' (alias ' . (is_int($mapping) ? '#' : '') . $mapping . ')';
            }

            $message->setVariable('param', $paramName);

            foreach ($values as $placeHolder => $value)
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

        /**
         * @return DataProcessorInterface
         */
        public function getDataProcessor() : DataProcessorInterface
        {
            return $this->dataProcessor;
        }

        /**
         * @param DataProcessorInterface $dataProcessor
         *
         * @return $this
         */
        public function setDataProcessor($dataProcessor)
        {

            $this->dataProcessor = $dataProcessor;

            return $this;
        }

    }