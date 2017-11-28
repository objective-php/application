<?php
use ObjectivePHP\Application\AbstractApplication;
use ObjectivePHP\Application\Config\Param;
use ObjectivePHP\Application\Middleware\WorkflowFiltersProviderInterface;
use ObjectivePHP\Events\EventsHandler;
use ObjectivePHP\PHPUnit\TestCase;
use ObjectivePHP\ServicesFactory\ServicesFactory;

class AbstractApplicationTest extends TestCase
{
    public function testDefaultEventsHandlerIsInstantiatedOnGet()
    {
        /**
         * @var $application AbstractApplication
         */
        $application = $this->getMockForAbstractClass(AbstractApplication::class);

        $eventsHandler = $application->getEventsHandler();

        $this->assertInstanceOf(EventsHandler::class, $eventsHandler);
    }

    public function testDefaultServicesFactoryIsInstantiatedOnGet()
    {
        /**
         * @var $application AbstractApplication
         */
        $application = $this->getMockForAbstractClass(AbstractApplication::class);

        $servicesFactory = $application->getServicesFactory();

        $this->assertInstanceOf(ServicesFactory::class, $servicesFactory);
    }

    public function testRun()
    {
        /**
         * @var $application AbstractApplication
         */
        $application = $this->getMockForAbstractClass(AbstractApplication::class);

        $application->addSteps('test');
        $application->getStep('test')->plug(function ()
        {
        })->as('first')
        ;
        $application->getStep('test')->plug(function ()
        {
        })->as('second')
        ;

        $application->run();

        $this->assertCount(2, $application->getExecutionTrace()['test']);
    }

    public function testRunFilteredStep()
    {
        /** @var AbstractApplication $app */
        $app = $this->getMockForAbstractClass(AbstractApplication::class);

        $app->addSteps('begin', 'end');
        $app->getStep('begin')->plug(function ()
        {
        })->as('first')
        ;
        $app->getStep('end')->plug(function ()
        {
        })->as('second')->addFilter(function() { return false;});

        $app->run();

        $this->assertNull($app->getExecutionTrace()['end']);
    }

    public function testParamsAreStoredInConfig()
    {
        $app = new class extends AbstractApplication {
            public function init() {}
        };
        $app->setParam('test.param', 'test.value');

        $this->assertEquals('test.value', $app->getParam('test.param'));
        $this->assertEquals('test.value', $app->getConfig()->subset(Param::class)->get('test.param'));
    }

    public function testParamsAreFetchedFromConfig()
    {
        $app = $this->getMockForAbstractClass(AbstractApplication::class);
        $app->getConfig()->import(new Param('test.param', 'test.value'));

        $this->assertEquals('test.value', $app->getParam('test.param'));
    }

    public function testRunWithMiddlewareFiltersProvider()
    {
        /** @var AbstractApplication $app */
        $app = $this->getMockForAbstractClass(AbstractApplication::class);

        $app->addSteps('test');

        $middleware = $this->getMockBuilder(WorkflowFiltersProviderInterface::class)->getMock();
        $middleware->expects($this->once())->method('runFilters')->willReturn(true);

        $app->getStep('test')->plug($middleware);

        $app->run();

        $this->assertCount(1, $app->getExecutionTrace()['test']);
    }

    public function testRunWithFilteredMiddlewareFiltersProvider()
    {
        /** @var AbstractApplication $app */
        $app = $this->getMockForAbstractClass(AbstractApplication::class);

        $app->addSteps('test');

        $middleware = $this->getMockBuilder(WorkflowFiltersProviderInterface::class)->getMock();
        $middleware->expects($this->once())->method('runFilters')->willReturn(false);

        $app->getStep('test')->plug(function () {
        });
        $app->getStep('test')->plug($middleware);
        $app->getStep('test')->plug(function () {
        });

        $app->run();

        $this->assertCount(2, $app->getExecutionTrace()['test']);
    }
}
