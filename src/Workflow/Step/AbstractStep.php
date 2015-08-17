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

    }