<?php

    namespace Test\ObjectivePHP\Application\Action\Param;
    
    
    use ObjectivePHP\Application\Action\Parameter\NumericParameter;
    use ObjectivePHP\Primitives\Numeric\Numeric;

    class NumericParameterTest extends \PHPUnit_Framework_TestCase
    {

        public function testValueProcessing()
        {

            $parameterValue = rand();

            $processor = new NumericParameter('reference');

            $this->assertEquals(new Numeric($parameterValue), $processor->process($parameterValue));

        }

    }