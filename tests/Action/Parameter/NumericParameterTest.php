<?php

    namespace Test\ObjectivePHP\Application\Action\Param;
    
    
    use ObjectivePHP\Application\Action\Parameter\NumericParameter;
    use ObjectivePHP\Application\Exception;
    use ObjectivePHP\PHPUnit\TestCase;
    use ObjectivePHP\Primitives\Numeric\Numeric;

    class NumericParameterTest extends TestCase
    {

        public function testValueProcessing()
        {

            $parameterValue = rand();

            $processor = new NumericParameter('reference');

            $this->assertEquals(new Numeric($parameterValue), $processor->process($parameterValue));

        }

        public function testMandatoryCheck()
        {
            $processor = (new NumericParameter('reference'))->setMandatory();

            $this->expectsException(function() use($processor)
            {
                $processor->process(null);
            }, Exception::class);
        }
    }