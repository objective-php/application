<?php

    namespace Test\ObjectivePHP\Application\Action\Param;
    
    
    use ObjectivePHP\Application\Action\Parameter\NumericParameterProcessor;
    use ObjectivePHP\Application\Exception;
    use ObjectivePHP\PHPUnit\TestCase;
    use ObjectivePHP\Primitives\Numeric\Numeric;

    class NumericParameterTest extends TestCase
    {

        public function testValueProcessing()
        {

            $parameterValue = rand();

            $processor = new NumericParameterProcessor('reference');

            $this->assertEquals($parameterValue, $processor->process($parameterValue));

            // transtyping test
            $this->assertSame(1, $processor->process("1"));
            $this->assertSame(1.0, $processor->process("1.0"));
            $this->assertSame(1.1, $processor->process("1.1"));


            $this->expectsException(function() use ($processor)
            {
                $parameterValue = "not a number";
                $processor->process($parameterValue);
            }, Exception::class);

        }

        public function testMandatoryCheck()
        {
            $processor = (new NumericParameterProcessor('reference'))->setMandatory();

            $this->expectsException(function() use($processor)
            {
                $processor->process(null);
            }, Exception::class);
        }
    }