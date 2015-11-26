<?php

namespace Test\ObjectivePHP\Application\Action\Param;


use ObjectivePHP\Application\Action\Parameter\AbstractParameterProcessor;
use ObjectivePHP\Application\Action\Parameter\ActionParameter;
use ObjectivePHP\Application\ApplicationInterface;

class AbstractParameterTest extends \PHPUnit_Framework_TestCase
{

    public function testConstructor()
    {
        // explicit mapping

        // with string
        $processor = $this->getMockForAbstractClass(AbstractParameterProcessor::class, ['reference', 'mapping']);

        $this->assertEquals('reference', $processor->getReference());
        $this->assertEquals('mapping', $processor->getQueryParameterMapping());

        // with integer (positional parameter)
        $processor = $this->getMockForAbstractClass(AbstractParameterProcessor::class, ['reference', 0]);

        $this->assertEquals('reference', $processor->getReference());
        $this->assertEquals(0, $processor->getQueryParameterMapping());

        // implicit mapping

        $processor = $this->getMockForAbstractClass(AbstractParameterProcessor::class, ['reference']);

        $this->assertEquals('reference', $processor->getReference());
        $this->assertEquals('reference', $processor->getQueryParameterMapping());

    }

    public function testMandatoryStateAccessors()
    {
        $processor = $this->getMockForAbstractClass(AbstractParameterProcessor::class, ['reference']);

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
        $processor = $this->getMockForAbstractClass(AbstractParameterProcessor::class, ['reference']);

        $app = $this->getMock(ApplicationInterface::class);

        $processor->setApplication($app);

        $this->assertAttributeSame($app, 'application', $processor);
        $this->assertSame($app, $processor->getApplication());
    }

    public function testErrorMessagesHandling()
    {
        $processor = $this->getMockForAbstractClass(AbstractParameterProcessor::class, ['reference']);

        // default message for missing parameter
        $this->assertContains('Missing mandatory parameter "reference', (string) $processor->getMessage());

        // set message for missing parameter
        $processor->setMessage(AbstractParameterProcessor::IS_MISSING, 'custom message');
        $this->assertEquals('custom message', $processor->getMessage(AbstractParameterProcessor::IS_MISSING));
    }
}