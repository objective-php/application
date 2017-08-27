<?php

use ObjectivePHP\Application\AbstractApplication;
use ObjectivePHP\Application\View\Plugin\AbstractPlugin;
use ObjectivePHP\Application\View\Plugin\Escaper;
use PHPUnit\Framework\TestCase;

class AbstractPluginTest extends TestCase
{
    public function testApplicationAccessor()
    {
        /** @var AbstractApplication $applicationMock */
        $applicationMock = $this->getMockBuilder(AbstractApplication::class)->getMock();

        $myPlugin = new MyPlugin();
        $myPlugin->setApplication($applicationMock);

        $this->assertEquals($myPlugin->getApplication(), $applicationMock);
        $this->assertAttributeEquals($myPlugin->getApplication(), 'application', $myPlugin);
    }
}

class MyPlugin extends AbstractPlugin
{
    /**
     * @param array ...$args
     * @return mixed
     */
    public function __invoke(...$args)
    {
        // TODO: Implement __invoke() method.
    }
}