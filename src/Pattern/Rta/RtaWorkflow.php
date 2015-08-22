<?php

    namespace ObjectivePHP\Application\Pattern\Rta;

    use ObjectivePHP\Application\Workflow\AbstractWorkflow;
    use ObjectivePHP\Application\Workflow\Workflow;

    /**
     * Class RtaWorkflow
     *
     * This workflow implements the Request to Action pattern
     *
     * @package ObjectivePHP\AppEngine\Rta
     */
    class RtaWorkflow extends AbstractWorkflow
    {
        /**
         * @param $name
         *
         * @throws \ObjectivePHP\Primitives\Exception
         */
        public function __construct($name = 'rta')
        {
            parent::__construct($name);


            /**
             * Application initialization
             *
             * This is where to:
             *
             *  - read application initial config
             *  - instantiate low level objects (like Request, EventsHandler and ServicesFactory)
             *
             */
            $this->addStep('init');


            /**
             * Packages handling
             *
             * This where to:
             *
             *  - load packages
             */
            $this->addStep($this->getPackageSubWorkflow());


            /**
             * Application bootstrapping
             *
             * This is where to:
             *
             *  - bootstrap application and modules
             *  - read and merge application and packages configs
             *
             */
            $this->addStep($this->getBoostrapSubWorkflow());

            /**
             * Action running
             */
            $this->addStep($this->getRunSubWorkflow());


            /**
             * Respond to client
             */
            $this->addStep('respond');

        }

        protected function getPackageSubWorkflow()
        {
            $packages = new Workflow('packages');
            $packages->addStep('load');

            return $packages;
        }

        public function getBoostrapSubWorkflow()
        {
            $bootstrap = new Workflow('bootstrap');
            $bootstrap->addStep('load-config');

            return $bootstrap;
        }

        public function getRunSubWorkflow()
        {
            $run = new Workflow('run');
            $run->addStep('route');
            $run->addStep('execute');
            $run->addStep('render');

            return $run;
        }
    }