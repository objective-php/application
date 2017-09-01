<?php

namespace Tests\ObjectivePHP\Application\Workflow\Filter;

use ObjectivePHP\Application\ApplicationInterface;
use ObjectivePHP\Application\Workflow\Filter\EnvFilter;

class EnvFilterTest extends \PHPUnit_Framework_TestCase
{


    /**
     * @param $validEnvironments
     * @param $actualEnvironment
     * @param $expected
     *
     * @dataProvider getDataForTestEnvFilter
     */
    public function testEnvFilter($validEnvironments, $actualEnvironment, $expected)
    {
        $app = $this->createMock(ApplicationInterface::class);
        $app->expects($this->once())->method('getEnv')->willReturn($actualEnvironment);
        $this->assertEquals($expected, (new EnvFilter($validEnvironments))->run($app));

    }

    public function getDataForTestEnvFilter()
    {
        return [
            ['prod', 'develop', false],
            ['!develop', 'production', true],
            [['test', '!production'], 'develop', true],
            [['test', '!production'], 'production', false],
        ];
    }
}