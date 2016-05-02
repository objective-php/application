<?php


    namespace Tests\ObjectivePHP\Application\Action;


    use ObjectivePHP\Application\Action\RenderableAction;
    use ObjectivePHP\PHPUnit\TestCase;

    class RenderableActionTest extends TestCase
    {

        public function testViewNameAccessors()
        {
            $action = $this->getMockForAbstractClass(RenderableAction::class);
            $action->setViewTemplate('view/name');
            $this->assertEquals('view/name', $action->getViewTemplate());
        }
    }