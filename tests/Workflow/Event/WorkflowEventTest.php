<?php

    namespace Tests\Workflow\Event;
    
    use ObjectivePHP\Application\ApplicationInterface;
    use ObjectivePHP\Application\Workflow\Event\WorkflowEvent;
    use ObjectivePHP\PHPUnit\TestCase;


    class WorkflowEventTest extends TestCase
    {

        public function testApplicationAccessors()
        {
            $workflow = new WorkflowEvent();
            $workflow->setApplication($app = $this->getMock(ApplicationInterface::class));
            $this->assertSame($app, $workflow->getApplication());
            $this->assertAttributeSame($app, 'application', $workflow);

        }

    }
