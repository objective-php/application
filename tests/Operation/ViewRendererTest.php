<?php

use ObjectivePHP\Application\AbstractApplication;
use ObjectivePHP\Application\Operation\ViewRenderer;
use ObjectivePHP\Application\View\Plugin\AbstractPlugin;
use ObjectivePHP\Application\View\Plugin\PluginInterface;
use PHPUnit\Framework\TestCase;
use ObjectivePHP\Application\Exception;

class ViewRendererTest extends TestCase
{
    public function testPluginWhenClassDoesNotExists()
    {
        $applicationMock = $this->getMockBuilder(AbstractApplication::class)->getMock();

        $viewRenderer = new ViewRenderer();
        $viewRenderer->setApplication($applicationMock);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('No helper found for FakeClass');
        $viewRenderer->plugin('FakeClass');
    }

    public function testPluginWhenClassNotPluginInterface()
    {
        $applicationMock = $this->getMockBuilder(AbstractApplication::class)->getMock();

        $viewRenderer = new ViewRenderer();
        $viewRenderer->setApplication($applicationMock);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage(sprintf('%s has to be an %s instance', stdClass::class, PluginInterface::class));
        $viewRenderer->plugin(stdClass::class);
    }

    public function testPlugin()
    {
        $applicationMock = $this->getMockBuilder(AbstractApplication::class)->getMock();

        $viewRenderer = new ViewRenderer();
        $viewRenderer->setApplication($applicationMock);

        $helper = $viewRenderer->plugin(MyHelper::class);

        $this->assertEquals((new MyHelper())
            ->setApplication($applicationMock),
            $helper
        );
    }
}

class MyHelper extends AbstractPlugin
{

    /**
     * @param array ...$args
     * @return mixed
     */
    public function __invoke(...$args)
    {
        return 'my-helper-content';
    }
}