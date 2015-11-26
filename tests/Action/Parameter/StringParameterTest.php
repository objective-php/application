<?php

    namespace Test\ObjectivePHP\Application\Action\Param;


    use ObjectivePHP\Application\Action\Parameter\StringParameter;
    use ObjectivePHP\Application\Exception;
    use ObjectivePHP\PHPUnit\TestCase;
    use ObjectivePHP\Primitives\String\Str;

    class StringParameterTest extends TestCase
    {

        public function testValueProcessing()
        {

            $parameterValue = uniqid();

            $processor = new StringParameter('reference');

            $this->assertEquals(new Str($parameterValue), $processor->process($parameterValue));

        }

        public function testMandatoryCheck()
        {
            $processor = (new StringParameter('reference'))->setMandatory();

            $this->expectsException(function () use ($processor)
            {
                $processor->process(null);
            }, Exception::class);
        }
    }