<?php

    namespace ObjectivePHP\Application\Action\Parameter;
    
    
    use ObjectivePHP\Application\ApplicationInterface;
    use ObjectivePHP\Primitives\Collection\Collection;

    interface ParameterProcessorInterface
    {

        /**
         * Define parameter name and map it to query parameters if needed
         *
         * @param string $reference    Parameter name (for further reference)
         * @param mixed  $queryMapping Related parameter name or position in the query string
         */
        public function __construct($reference, $queryMapping = null);

        /**
         * Inject Application
         *
         * @param ApplicationInterface $application
         *
         * @return mixed
         */
        public function setApplication(ApplicationInterface $application);

        /**
         * Get injected Application
         *
         * @return ApplicationInterface
         */
        public function getApplication();

        /**
         * Get parameter reference
         *
         * @return string
         */
        public function getReference();

        /**
         * Set mandatory state
         *
         * @param bool|true $switch
         *
         * @return mixed
         */
        public function setMandatory($switch = true);

        /**
         * Tells whether the parameter is mandatory or not
         *
         * @return bool
         */
        public function isMandatory();

        /**
         * Process a value
         *
         * The processed value will be stored as parameter value
         *
         * @param mixed $value
         *
         * @return mixed
         */
        public function process($value);

        /**
         * @return Collection
         */
        public function getMessages();

        /**
         * Get error message for given error code
         *
         * @return mixed
         */
        public function getMessage($code = ActionParameter::IS_MISSING);

        /**
         * Set error message for given error code
         *
         * @param mixed  $code
         * @param string $message
         *
         * @return mixed
         */
        public function setMessage($code, $message);

        /**
         * @return string
         */
        public function getQueryParameterMapping();
    }