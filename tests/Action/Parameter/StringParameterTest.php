<?php

    namespace Test\ObjectivePHP\Application\Action\Param;
    
    
    use ObjectivePHP\Application\Action\Parameter\StringParameter;
    use ObjectivePHP\Primitives\String\String;

    class StringParameterTest extends \PHPUnit_Framework_TestCase
    {

        public function testValueProcessing()
        {

            $parameterValue = uniqid();

            $processor = new StringParameter('reference');

            $this->assertEquals(new String($parameterValue), $processor->process($parameterValue));

        }

    }