<?php
    /**
     * Created by PhpStorm.
     * User: gauthier
     * Date: 10/09/15
     * Time: 22:02
     */
    
    namespace Test\ObjectivePHP\Application\Workflow\Step;
    
    
    use ObjectivePHP\Application\Workflow\Step\AbstractStep;
    use ObjectivePHP\PHPUnit\TestCase;

    class AbstractStepTest extends TestCase
    {

        public function testDescriptionAccessors()
        {
            $step = $this->getMockForAbstractClass(AbstractStep::class, ['name']);
            $step->setDescription('description');
            $this->assertAttributeEquals('description', 'description', $step);
            $this->assertEquals('description', $step->getDescription());
        }

        public function testDocumentationAccessors()
        {
            $step = $this->getMockForAbstractClass(AbstractStep::class, ['name']);
            $step->setDocumentation('documentation');
            $this->assertAttributeEquals('documentation', 'documentation', $step);
            $this->assertEquals('documentation', $step->getDocumentation());
        }

    }