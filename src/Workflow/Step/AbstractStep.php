<?php

    namespace ObjectivePHP\Application\Workflow\Step;
    
    
    use ObjectivePHP\Primitives\Collection\Collection;

    abstract class AbstractStep implements StepInterface
    {
        /**
         * Children steps
         *
         * @var Collection
         */
        protected $steps;

        /**
         * @var string Event identifier
         */
        protected $name;

        /**
         * Short description
         *
         * @var string Step description (for documentation purpose)
         */
        protected $description;

        /**
         * Detailed information about step role in workflow
         *
         * @var string
         */
        protected $documentation;

        /**
         * @param $name
         */
        public function __construct($name)
        {
            $this->setName($name);

            $this->steps = (new Collection())->restrictTo(StepInterface::class);
        }

        /**
         * @return string
         */
        public function getName()
        {
            return $this->name;
        }

        /**
         * @param string $name
         *
         * @return $this
         */
        public function setName($name)
        {
            $this->name = $name;

            return $this;
        }

        /**
         * @return string
         */
        public function getDescription()
        {
            return $this->description;
        }

        /**
         * @param string $description
         *
         * @return $this
         */
        public function setDescription($description)
        {
            $this->description = $description;

            return $this;
        }

        /**
         * @return string
         */
        public function getDocumentation()
        {
            return $this->documentation;
        }

        /**
         * @param string $documentation
         *
         * @return $this
         */
        public function setDocumentation($documentation)
        {
            $this->documentation = $documentation;

            return $this;
        }


    }