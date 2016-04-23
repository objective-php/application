<?php
/**
 * Created by PhpStorm.
 * User: gauthier
 * Date: 18/04/2016
 * Time: 23:10
 */

namespace Test\ObjectivePHP\Application\Workflow;


use ObjectivePHP\Application\Workflow\Hook;
use ObjectivePHP\Application\Workflow\Step;
use ObjectivePHP\PHPUnit\TestCase;

class StepTest extends TestCase
{
    /**
     * @throws \ObjectivePHP\Application\Exception
     * @throws \ObjectivePHP\Primitives\Exception
     */
    public function testAliasing()
    {

        $step = new Step('test');

        $step->plug($firstMiddleware = function() {})->as('first-middleware');

        /** @var Hook $embeddedMiddleware */
        $embeddedMiddleware = $step->get('first-middleware');
        $this->assertAttributeSame($firstMiddleware, 'operation', $embeddedMiddleware->getMiddleware()->getInvokable());

        $step->plug($secondMiddleware = function() {})->as('second-middleware');
        $step->plug($defaultSecondMiddleware = function() {})->asDefault('second-middleware');

        /** @var Hook $embeddedMiddleware */
        $embeddedMiddleware = $step->get('second-middleware');
        $this->assertAttributeSame($secondMiddleware, 'operation', $embeddedMiddleware->getMiddleware()->getInvokable());

    }
}