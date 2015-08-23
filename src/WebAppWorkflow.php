<?php

    namespace ObjectivePHP\Application;

    use ObjectivePHP\Application\Workflow\AbstractWorkflow;
    use ObjectivePHP\Application\Workflow\Step\Step;
    use ObjectivePHP\Application\Workflow\Workflow;

    /**
     * Class WebAppWorkflow
     *
     * This workflow implements the Request to Action pattern
     *
     * @package ObjectivePHP\AppEngine\Rta
     */
    class WebAppWorkflow extends AbstractWorkflow
    {
        /**
         * @param $name
         *
         * @throws \ObjectivePHP\Primitives\Exception
         */
        public function __construct($name = 'app')
        {
            parent::__construct($name);


            /**
             * Application initialization
             */
            $init = new Step('init');
            $init->setDescription('Application initialization');
            $init->setDocumentation(
                'This is where to: ' . PHP_EOL
                . PHP_EOL
                . ' - read application initial config' . PHP_EOL
                . ' - instantiate low level objects (like Request, EventsHandler and ServicesFactory)' . PHP_EOL
                . ' - handle environment'
            );
            $this->addStep($init);


            $bootstrap = (new Step('bootstrap'))->setDescription('Application bootstrapping');
            $this->addStep($bootstrap);

            /**
             * Packages handling
             *
             */
            $this->addStep($this->getPackageSubWorkflow());


            /**
             * Routing
             */
            $this->addStep($this->getRoutingSubWorkflow());

            /**
             * Action running
             */
            $this->addStep($this->getRunSubWorkflow());

            /**
             * Respond to client
             */
            $response = (new Workflow('response'))->setDescription('Send response to the client');
            $response->addStep((new Step('generate'))->setDescription('Produce response content'));
            $this->addStep($response);

        }

        protected function getPackageSubWorkflow()
        {
            $packages = new Workflow('packages');
            $packages->setDescription('Packages handling');

            $load = (new Step('load'))
                ->setDescription('Bootstrap packages')
                ->setDocumentation('This is where packages will merge their configuration into application config');
            $packages->addStep($load);

            return $packages;
        }

        public function getRoutingSubWorkflow()
        {
            $routing = new Workflow('route');
            $routing->setDescription('Request routing');
            $routing->setDocumentation('This where to automate tasks depending on defined route (on `route.post` event)');

            $resolve = (new Step('resolve'))->setDescription('Define what action has to be run. Default callback is aliased to `action-resolver`.');
            $routing->addStep($resolve);

            return $routing;
        }

        public function getRunSubWorkflow()
        {
            $run = new Workflow('run');
            $run->setDescription('Actual action execution');

            $execute = (new Step('execute'))
                        ->setDescription('Execute action defined by routing')
                        ->setDocumentation('An action is a callback, thus a callable, that has to be bound to this event');
            $run->addStep($execute);


            return $run;
        }

        public function getRenderingSubWorkflow()
        {
            $rendering = (new Workflow('render'))->setDescription('Generate displayable content');

            $resolve = (new Step('view.resolve'))
                ->setDescription('Compute information needed for the renderer (like a view script name)')
                ->setDocumentation('The default callback bound to this event is named `view-resolver`, to ease getting what it returns later in the workflow')
            ;
            $rendering->addStep($resolve);

            $view = (new Step('view.generate'))->setDescription('Actually renders the displayable content');
            $rendering->addStep($view);

            $layoutResolver = (new Step('layout.resolve'))->setDescription('Same as `view.resolve` but for the layout script');
            $rendering->addStep($layoutResolver);

            $layoutRenderer = (new Step('layout.generate'))->setDescription('Wrap displayable content in a layout');
            $rendering->addStep($layoutRenderer);

            return $rendering;
        }
    }