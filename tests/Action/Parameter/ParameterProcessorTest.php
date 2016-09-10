<?php

    namespace Test\ObjectivePHP\Application\Action\Param;


    use ObjectivePHP\Application\Action\Parameter\ParameterProcessor;
    use ObjectivePHP\Application\Action\Parameter\ActionParameter;
    use ObjectivePHP\Application\ApplicationInterface;
    use ObjectivePHP\DataProcessor\StringProcessor;

    class ParameterProcessorTest extends \PHPUnit_Framework_TestCase
    {

        public function testConstructor()
        {
            // explicit mapping

            // with string
            $processor = $this->getMockForAbstractClass(ParameterProcessor::class, [new StringProcessor(), 'reference', 'mapping']);
            
            $this->assertEquals('reference', $processor->getReference());
            $this->assertEquals('mapping', $processor->getQueryParameterMapping());

            // with integer (positional parameter)
            $processor = $this->getMockForAbstractClass(ParameterProcessor::class, [new StringProcessor(), 'reference', 0]);

            $this->assertEquals('reference', $processor->getReference());
            $this->assertEquals(0, $processor->getQueryParameterMapping());

            // implicit mapping

            $processor = $this->getMockForAbstractClass(ParameterProcessor::class, [new StringProcessor(), 'reference']);

            $this->assertEquals('reference', $processor->getReference());
            $this->assertEquals('reference', $processor->getQueryParameterMapping());

        }

        public function testMandatoryStateAccessors()
        {
            $processor = $this->getMockForAbstractClass(ParameterProcessor::class, [new StringProcessor(), 'reference']);

            // check default state
            $this->assertFalse($processor->isMandatory());

            // explicitly set mandatory
            $processor->setMandatory(true);
            $this->assertTrue($processor->isMandatory());


            // explicitly set not mandatory
            $processor->setMandatory(false);
            $this->assertFalse($processor->isMandatory());

            // implicitly set mandatory
            $processor->setMandatory();
            $this->assertTrue($processor->isMandatory());

        }

        public function testApplicationAccessors()
        {
            $processor = $this->getMockForAbstractClass(ParameterProcessor::class, [new StringProcessor(), 'reference']);

            $app = $this->createMock(ApplicationInterface::class);

            $processor->setApplication($app);

            $this->assertAttributeSame($app, 'application', $processor);
            $this->assertSame($app, $processor->getApplication());
        }

        public function testErrorMessagesHandling()
        {
            $processor = $this->getMockForAbstractClass(ParameterProcessor::class, [new StringProcessor(), 'reference']);

            // default message for missing parameter
            $this->assertContains('Missing mandatory parameter "reference', (string) $processor->getMessage());

            // set message for missing parameter
            $processor->setMessage(ParameterProcessor::IS_MISSING, 'custom message');
            $this->assertEquals('custom message', $processor->getMessage(ParameterProcessor::IS_MISSING));
        }
    }
