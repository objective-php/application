<?php
/**
 * This file is part of the Objective PHP project
 *
 * More info about Objective PHP on www.objective-php.org
 *
 * @license http://opensource.org/licenses/GPL-3.0 GNU GPL License 3.0
 */

namespace Tests\ObjectivePHP\Application\Action\Paramter\Cli;


use ObjectivePHP\Application\Action\Parameter\Cli\Param;

class ParamTest extends \PHPUnit_Framework_TestCase
{
    
    /**
     * @dataProvider getDataForTestHydration
     */
    public function testHydration($cli, $name, $expectedValue, $remainingCli)
    {
        if ($expectedValue instanceof \Exception)
        {
            $this->expectException($expectedValue);
        }
        
        $param = new Param($name);
        
        $cliAfterHydration = $param->hydrate($cli);
        
        $this->assertEquals($expectedValue, $param->getValue());
        $this->assertEquals($remainingCli, $cliAfterHydration);
    }
    
    public function getDataForTestHydration()
    {
        return
            [
                ['-e value1', 'e', 'value1', ''],
                ['-e "value1"', 'e', 'value1', ''],
                ['-e \'value1\'', 'e', 'value1', ''],
                ['-e value1 -v', 'e', 'value1', '-v'],
                ['--param value1 -v', ['p' => 'param'], 'value1', '-v'],
            ];
    }
    /*
    public function testHydrationChain()
    {
        $param1 = new Param(['v' => 'verbose']);
        $param2 = new Param('e');
        
        $cli = '-vev arg1';
        
        $cli = $param1->hydrate($cli);
        $this->assertEquals('-e arg1', $cli);
        $this->assertEquals(2, $param1->getValue());
        $cli = $param2->hydrate($cli);
        $this->assertEquals('arg1', $cli);
        
    }
    */
}
