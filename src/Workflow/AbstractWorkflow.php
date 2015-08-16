<?php

    namespace ObjectivePHP\Application\Workflow;

    use ObjectivePHP\Application\ApplicationInterface;
    use ObjectivePHP\Application\Workflow\Step\Step;
    use ObjectivePHP\Events\Event;
    use ObjectivePHP\Events\EventInterface;
    use ObjectivePHP\Events\EventsHandler;
    use ObjectivePHP\Primitives\Collection\Collection;
    use ObjectivePHP\Application\Workflow\Step\AbstractStep;
    use ObjectivePHP\Application\Workflow\Step\StepInterface;
    use ObjectivePHP\Application\Workflow\Event\WorkflowEvent;

    abstract class AbstractWorkflow extends AbstractStep implements WorkflowInterface
    {
        /**
         * @var Collection
         */
        protected $steps;

        /**
         * @var bool
         */
        protected $autoTriggerPrePostEvents = true;

        /**
         * @var EventsHandler
         */
        protected $eventsHandler;

        /**
         * @var WorkflowInterface
         */
        protected $parent;

        /**
         * @var Collection
         */
        protected $events;


        /**
         * @var ApplicationInterface
         */
        protected $application;

        /**
         * @param $name
         *
         * @throws \ObjectivePHP\Primitives\Exception
         */
        public function __construct($name = 'workflow')
        {
            parent::__construct($name);

            $this->steps = (new Collection())->restrictTo(StepInterface::class);
            $this->events = (new Collection())->restrictTo(EventInterface::class);

        }

        /**
         * Actually runs the workflow
         */
        public function run()
        {
            if ($this->doesAutoTriggerPrePostEvents())
            {
                $this->triggerStep('pre');
            }

            foreach ($this->steps as $step)
            {
                if ($step instanceof WorkflowInterface)
                {
                    $step->setEventsHandler($this->getEventsHandler());
                    $step->setApplication($this->getApplication());
                    $step->setParent($this);

                    // run sub workflow
                    $step->run();
                }
                else
                {
                    $this->triggerStep($step->getName());
                }

            }

            if ($this->doesAutoTriggerPrePostEvents())
            {
                $this->triggerStep('post');
            }
        }

        protected function triggerStep($step)
        {
            $event = (new WorkflowEvent())->setApplication($this->getApplication());

            $this->getEvents()->append($this->getEventsHandler()->trigger($this->computeEventFullyQualifiedName($step), $this, null, $event));
        }

        protected function computeEventFullyQualifiedName($step)
        {

            $prefix = [$this->getName()];
            $parent = $this->getParent();

            while($parent)
            {
                $prefix[] = $parent->getName();
                $parent = $parent->getParent();
            }

            $prefix = array_reverse($prefix);

            return  implode('.', $prefix) . '.' . $step;

        }

        /**
         * @param StepInterface ...$steps
         *
         * @return $this
         */
        public function addStep(...$steps)
        {
            foreach($steps as $step)
            {
                if(!$step instanceof StepInterface)
                {
                    $step = new Step($step);
                }
                $this->steps->append($step);
            }

            return $this;
        }

        /**
         * @return Collection
         */
        public function getSteps()
        {
            return $this->steps;
        }

        /**
         * @return boolean
         */
        public function doesAutoTriggerPrePostEvents()
        {
            return $this->autoTriggerPrePostEvents;
        }

        /**
         * @param boolean $autoTriggerPrePostEvents
         *
         * @return $this
         */
        public function autoTriggerPrePostEvents($autoTriggerPrePostEvents)
        {
            $this->autoTriggerPrePostEvents = (bool) $autoTriggerPrePostEvents;

            return $this;
        }

        /**
         * @return EventsHandler
         */
        public function getEventsHandler()
        {
            return $this->eventsHandler;
        }

        /**
         * @param EventsHandler $eventsHandler
         *
         * @return $this
         */
        public function setEventsHandler(EventsHandler $eventsHandler)
        {
            $this->eventsHandler = $eventsHandler;

            return $this;
        }

        /**
         * @return WorkflowInterface
         */
        public function getParent()
        {
            return $this->parent;
        }

        /**
         * @param WorkflowInterface $parent
         *
         * @return $this
         */
        public function setParent(WorkflowInterface $parent)
        {
            $this->parent = $parent;

            return $this;
        }

        /**
         * @return Collection
         */
        public function getEvents()
        {
            return $this->events;
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

        public function bind($event, $callback, $mode = EventsHandler::BINDING_MODE_LAST)
        {
            $eventFullyQualifiedName = $this->computeEventFullyQualifiedName($event);

            $this->getEventsHandler()->bind($eventFullyQualifiedName, $callback, $mode);

            return $this;
        }

    }