<?php

use ObjectivePHP\Application\AbstractApplication;
use ObjectivePHP\Application\Workflow\Event as WorkflowEvent;
use ObjectivePHP\Events\EventsHandler;
use ObjectivePHP\PHPUnit\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\EmitterInterface;


class AbstractApplicationTest extends TestCase
{
    /**
     * @test
     *
     * ObjectivePHP application trigger 9 main events to allow developers customize the workflow.
     * It MUST trigger consecutively :
     *     - ObjectivePHP\Application\Workflow\Event::BOOTSTRAP_INIT
     *     - ObjectivePHP\Application\Workflow\Event::BOOTSTRAP_DONE
     *     - ObjectivePHP\Application\Workflow\Event::PACKAGES_INIT
     *     - ObjectivePHP\Application\Workflow\Event::PACKAGES_READY
     *     - ObjectivePHP\Application\Workflow\Event::ROUTING_START
     *     - ObjectivePHP\Application\Workflow\Event::ROUTING_DONE
     *     - ObjectivePHP\Application\Workflow\Event::REQUEST_HANDLING_START
     *     - ObjectivePHP\Application\Workflow\Event::REQUEST_HANDLING_DONE
     *     - ObjectivePHP\Application\Workflow\Event::RESPONSE_SENT
     */
    public function applicationTriggerWorkflowEvents()
    {
        $spy = $this->createMock(EventsHandler::class);
        $spy
            ->expects($this->exactly(9))
            ->method('trigger')
            ->withConsecutive(
                [WorkflowEvent::BOOTSTRAP_INIT],
                [WorkflowEvent::BOOTSTRAP_DONE],
                [WorkflowEvent::PACKAGES_INIT],
                [WorkflowEvent::PACKAGES_READY],
                [WorkflowEvent::ROUTING_START],
                [WorkflowEvent::ROUTING_DONE],
                [WorkflowEvent::REQUEST_HANDLING_START],
                [WorkflowEvent::REQUEST_HANDLING_DONE],
                [WorkflowEvent::RESPONSE_SENT]
            );

        $application = $this->getMockBuilder(AbstractApplication::class)
            ->setConstructorArgs([null, $spy])
            ->setMethods(['getRequest', 'handle'])
            ->getMockForAbstractClass();

        $application
            ->method('getRequest')
            ->willReturn($this->createMock(ServerRequestInterface::class));

        $application->run($this->createMock(EmitterInterface::class));
    }
}

