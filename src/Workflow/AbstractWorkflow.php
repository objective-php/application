<?php

    namespace ObjectivePHP\Application\Workflow;

    use ObjectivePHP\Application\ApplicationInterface;
    use ObjectivePHP\Application\Exception;
    use ObjectivePHP\Application\Workflow\Event\WorkflowEvent;
    use ObjectivePHP\Application\Workflow\Step\AbstractStep;
    use ObjectivePHP\Application\Workflow\Step\Step;
    use ObjectivePHP\Application\Workflow\Step\StepInterface;
    use ObjectivePHP\Events\EventInterface;
    use ObjectivePHP\Events\EventsHandler;
    use ObjectivePHP\Events\Exception as EventsException;
    use ObjectivePHP\Primitives\Collection\Collection;

    /**
     * Class AbstractWorkflow
     *
     * @package ObjectivePHP\Application\Workflow
     */
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
         * @var bool
         */
        protected $isHalted = true;

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

            $this->steps  = (new Collection())->restrictTo(StepInterface::class);
            $this->events = (new Collection())->restrictTo(EventInterface::class);

        }

        /**
         * @param StepInterface ...$steps
         *
         * @return $this
         */
        public function addStep(...$steps)
        {
            foreach ($steps as $step)
            {
                if (!$step instanceof StepInterface)
                {
                    $step = new Step($step);
                }
                $this->steps->set($step->getName(), $step);
            }

            return $this;
        }

        /**
         * @param        $event
         * @param        $callback
         * @param string $mode
         *
         * @return $this
         * @throws Exception
         */
        public function bind($event, $callback, $mode = EventsHandler::BINDING_MODE_LAST)
        {
            $eventFullyQualifiedName = $this->computeEventFullyQualifiedName($event);

            $event = (new WorkflowEvent())->setApplication($this->getApplication());

            return $this;
        }

        /**
         * @return boolean
         */
        public function doesAutoTriggerPrePostEvents()
        {
            return $this->autoTriggerPrePostEvents;
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
         * @return WorkflowInterface
         */
        public function getRoot()
        {
            $parent = $this;

            while (true)
            {
                $lastParent = $parent->getParent();
                if (!$lastParent)
                {
                    break;
                }
                else $parent = $lastParent;
            }

            return $parent;

        }

        /**
         * @param $step
         *
         * @return StepInterface
         */
        public function getStep($step)
        {
            return isset($this->steps[$step]) ? $this->steps[$step] : null;
        }

        /**
         * @return Collection
         */
        public function getSteps()
        {
            return $this->steps;
        }

        /**
         * Halt current workflow execution
         *
         * @return $this
         */
        public function halt()
        {
            $this->isHalted = true;

            // propagate to parents
            $parent = $this;
            while ($parent = $parent->getParent())
            {
                $parent->halt();
            }

            // also halt current event
            $this->getRoot()->getEvents()->last()->halt();

            return $this;
        }

        /**
         * Actually runs the workflow
         */
        public function run()
        {
            $this->isHalted = false;

            if ($this->doesAutoTriggerPrePostEvents())
            {
                $this->triggerStep('pre');
            }

            foreach ($this->steps as $step)
            {

                // stop execution if the Workflow has been stopped
                if ($this->isHalted()) goto shunt;

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
                    //run current step
                    $this->triggerStep($step->getName());
                }

            }

            if ($this->doesAutoTriggerPrePostEvents())
            {
                $this->triggerStep('post');
            }

            return true;

            // workflow has been halted
            shunt:
                return false;
        }

        /**
         * @param $event
         *
         * @return $this
         */
        public function unbind($event)
        {
            $eventFullyQualifiedName = $this->computeEventFullyQualifiedName($event);

            $this->getEventsHandler()->unbind($eventFullyQualifiedName);

            return $this;
        }

        /**
         * @param $step
         *
         * @throws EventsException
         * @throws \ObjectivePHP\Primitives\Exception
         */
        protected function triggerStep($step)
        {
            $event     = (new WorkflowEvent())->setApplication($this->getApplication());
            $eventName = $this->computeEventFullyQualifiedName($step);

            // store event in stack
            $this->getRoot()->getEvents()->set($eventName, $event);

            // actually triggers related event
            $this->getEventsHandler()->trigger($eventName, $this, [], $event);

        }

        /**
         * @param $step
         *
         * @return string
         */
        public function computeEventFullyQualifiedName($step)
        {

            $prefix = [$this->getName()];
            $parent = $this->getParent();

            while ($parent)
            {
                $prefix[] = $parent->getName();
                $parent   = $parent->getParent();
            }

            $prefix = array_reverse($prefix);

            // if step starts with path root, return it as is
            if ($stepParts = explode('.', $step))
            {
                if ($stepParts[0] == $prefix[0])
                {
                    return $step;
                }
            }

            return implode('.', $prefix) . '.' . $step;

        }

        /**
         * @return Collection
         */
        public function getEvents()
        {
            return $this->events;
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
         * @return bool
         */
        public function isHalted()
        {
            return $this->isHalted;
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
         * Return an Event object related to a previously ran step
         *
         * @param $step
         *
         * @return WorkflowEvent|null
         */
        public function getEarlierEvent($step)
        {
            $fullyQualifiedEventName = $this->computeEventFullyQualifiedName($step);

            $event = $this->getRoot()->events->get($fullyQualifiedEventName);

            return $event;
        }
    }