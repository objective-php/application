<?php

    namespace Workflow\Test;
    
    use ObjectivePHP\Application\ApplicationInterface;
    use ObjectivePHP\Application\Exception;
    use ObjectivePHP\Application\Workflow\Event\WorkflowEvent;
    use ObjectivePHP\Application\Workflow\Step\Step;
    use ObjectivePHP\Application\Workflow\Workflow;
    use ObjectivePHP\Events\Event;
    use ObjectivePHP\Events\EventsHandler;
    use ObjectivePHP\PHPUnit\TestCase;


    class AbstractWorkflowTest extends TestCase
    {

        public function testEventsHandlerAccessors()
        {

            $eventsHandler = $this->getMock(EventsHandler::class);

            $workflowEngine = new Workflow('main');

            $workflowEngine->setEventsHandler($eventsHandler);

            $this->assertAttributeSame($eventsHandler, 'eventsHandler', $workflowEngine);
            $this->assertSame($eventsHandler, $workflowEngine->getEventsHandler());
        }

        public function testRun()
        {

            $step1 = new Step('event.first');
            $step2 = (new Workflow('sub-process'))->addStep(new Step('event'));
            $step3 = new Step('event.third');

            $workflow = new Workflow();
            $workflow->setApplication($this->getMock(ApplicationInterface::class));
            $eventsHandler = $this->getMock(EventsHandler::class);
            $eventsHandler->expects($this->at(0))->method('trigger')->with('workflow.pre', $workflow)
                          ->willReturn($this->getMock(Event::class))
            ;
            $eventsHandler->expects($this->at(1))->method('trigger')->with('workflow.event.first', $workflow)
                          ->willReturn($this->getMock(Event::class))
            ;
            $eventsHandler->expects($this->at(2))->method('trigger')->with('workflow.sub-process.pre', $step2)
                          ->willReturn($this->getMock(Event::class))
            ;
            $eventsHandler->expects($this->at(3))->method('trigger')->with('workflow.sub-process.event', $step2)
                          ->willReturn($this->getMock(Event::class))
            ;
            $eventsHandler->expects($this->at(4))->method('trigger')->with('workflow.sub-process.post', $step2)
                          ->willReturn($this->getMock(Event::class))
            ;
            $eventsHandler->expects($this->at(5))->method('trigger')->with('workflow.event.third', $workflow)
                          ->willReturn($this->getMock(Event::class))
            ;
            $eventsHandler->expects($this->at(6))->method('trigger')->with('workflow.post', $workflow)
                          ->willReturn($this->getMock(Event::class))
            ;

            $workflow->setEventsHandler($eventsHandler);

            $workflow->addStep($step1, $step2, $step3);

            $workflow->run();

        }

        public function testApplicationAccessors()
        {
            $workflow = (new Workflow('main'))->autoTriggerPrePostEvents(false);
            $workflow->setApplication($app = $this->getMock(ApplicationInterface::class));
            $this->assertSame($app, $workflow->getApplication());
            $this->assertAttributeSame($app, 'application', $workflow);

        }

        public function testEventsAreCollectedIntoWorkflow()
        {
            $step1    = new Step('event.first');
            $step2    = new Step('event.second');
            $workflow = (new Workflow('main'))->autoTriggerPrePostEvents(false);
            $workflow->setApplication($app = $this->getMock(ApplicationInterface::class));

            $eventsHandler = $this->getMock(EventsHandler::class);
            $eventsHandler->expects($this->at(0))->method('trigger')->with('main.event.first', $workflow)
                          ->willReturn($this->getMock(Event::class))
            ;
            $eventsHandler->expects($this->at(1))->method('trigger')->with('main.event.second', $workflow)
                          ->willReturn($this->getMock(Event::class))
            ;
            $workflow->setEventsHandler($eventsHandler);
            $workflow->addStep($step1, $step2);

            $this->assertTrue($workflow->getEvents()->isEmpty());

            $workflow->run();

            $this->assertcount(2, $workflow->getEvents());

        }

        public function testBindPrefixesEventNameWithCurrentWorkflowFQN()
        {
            $workflow = (new Workflow('main'))->autoTriggerPrePostEvents(false);

            $callback = function ()
            {
            };

            $eventsHandler = $this->getMock(EventsHandler::class);
            $eventsHandler->expects($this->once())->method('bind')
                          ->with('main.init', $callback, EventsHandler::BINDING_MODE_LAST)
            ;

            $workflow->setEventsHandler($eventsHandler);

            $workflow->bind('init', $callback);

        }

        public function testBindingAnInvalidCallbackTOAnEventFailsWithAnException()
        {
            $workflow      = new Workflow();
            $eventsHandler = $this->getMock(EventsHandler::class);
            $eventsHandler->expects($this->once())->method('bind')
                          ->willThrowException(new \ObjectivePHP\Events\Exception(''))
            ;;
            $workflow->setEventsHandler($eventsHandler);

            $this->expectsException(function () use ($workflow)
            {
                $workflow->bind('whatever', 'this is not a callable');
            }, Exception::class, null, Exception::INVALID_EVENT_BINDING);
        }

        public function testAddStepAcceptsStrings()
        {
            $workflow = new Workflow();

            $workflow->addStep('first-step');

            $this->assertInstanceOf(Step::class, $workflow->getSteps()->first());
            $this->assertSame($workflow->getStep('first-step'), $workflow->getSteps()->first());
        }

        public function testEarlierEventAccess()
        {
            $workflow = new Workflow();
            $workflow->setApplication($app = $this->getMock(ApplicationInterface::class));
            $workflow->setEventsHandler(new EventsHandler);

            $workflow->addStep('first-step', 'second-step');

            $workflow->run();

            $this->assertInstanceOf(WorkflowEvent::class, $workflow->getEarlierEvent('first-step'));
            $this->assertInstanceOf(WorkflowEvent::class, $workflow->getEarlierEvent('second-step'));

        }
    }

