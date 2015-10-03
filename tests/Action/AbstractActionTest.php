<?php

    namespace Tests\ObjectivePHP\Application\Action;

    use ObjectivePHP\Application\Action\AbstractAction;
    use ObjectivePHP\Application\Action\Parameter\NumericParameter;
    use ObjectivePHP\Application\Action\Parameter\StringParameter;
    use ObjectivePHP\Application\ApplicationInterface;
    use ObjectivePHP\Application\Workflow\Event\WorkflowEvent;
    use ObjectivePHP\Events\EventsHandler;
    use ObjectivePHP\Message\Request\Parameter\Container\HttpParameterContainer;
    use ObjectivePHP\Message\Request\RequestInterface;
    use ObjectivePHP\PHPUnit\TestCase;
    use ObjectivePHP\Primitives\Numeric\Numeric;
    use ObjectivePHP\Primitives\String\String;
    use ObjectivePHP\ServicesFactory\ServicesFactory;

    class AbstractActionTest extends TestCase
    {

        public function testActionInvokationExecuteRunMethod()
        {
            $event = $this->getEvent();

            $action = $this->getAction();
            $action->expects($this->once())->method('run')->with($event);

            call_user_func($action, $event);
        }

        public function testParametersProcessing()
        {
            $event = $this->getEvent(['param1_value', 'param2' => 12]);

            $action = $this->getAction();
            $action->setParameterProcessor(new StringParameter('param1', 0), new NumericParameter('param2'));
            $action->expects($this->at(0))->method('processParameters');

            call_user_func($action, $event);

            $this->assertInstanceOf(String::class, $action->getParams()['param1']);
            $this->assertEquals(new String('param1_value'), $action->getParam('param1'));
            $this->assertEquals(new Numeric(12), $action->getParam('param2'));

            $action->setParam('param1', 'updated_param1_value');

            $this->assertInstanceOf(String::class, $action->getParams()['param1']);
            $this->assertEquals(new String('updated_param1_value'), $action->getParams()['param1']);

            // also test unprocessed params
            $action->setParam('param3', 'param3_value');
            $this->assertEquals('param3_value', $action->getParams()['param3']);

        }

        public function testServicesFactoryProxy()
        {
            $action = $this->getAction();
            $action->__invoke($this->getEvent());

            $action->getServicesFactory()->expects($this->once())->method('get')->with('service.id');

            $action->getService('service.id');
        }

        public function testEventsHandlerAccessors()
        {
            $action = $this->getAction();
            $action->setEventsHandler($eventsHandler = new EventsHandler());

            $this->assertSame($eventsHandler, $action->getEventsHandler());
        }


        protected function getAction()
        {

            $action = $this->getMockForAbstractClass(AbstractAction::class, [], '', true, true, true, ['run']);


            return $action;
        }

        public function getEvent($requestParameters = [])
        {
            $event = new WorkflowEvent();
            $request = $this->getMock(RequestInterface::class);

            $parameters = $this->getMock(HttpParameterContainer::class, [], [$request]);
            $parameters->expects($this->any())->method('fromGet')->willReturn($requestParameters);

            $request->expects($this->any())->method('getParameters')->willReturn($parameters);


            $application = $this->getMockForAbstractClass(ApplicationInterface::class);
            $application->expects($this->any())->method('getServicesFactory')
                        ->willReturn($this->getMock(ServicesFactory::class))
            ;
            $application->expects($this->any())->method('getRequest')->willReturn($request);

            $event->setApplication($application);

            return $event;
        }
    }