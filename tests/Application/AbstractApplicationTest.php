<?php

use ObjectivePHP\Application\AbstractHttpApplication;
use ObjectivePHP\Application\Config\Param;
use ObjectivePHP\Events\EventsHandler;
use ObjectivePHP\PHPUnit\TestCase;
use ObjectivePHP\ServicesFactory\ServicesFactory;


class AbstractApplicationTest extends TestCase
{
    
    
    public function testDefaultEventsHandlerIsInstantiatedOnGet()
    {
        /**
         * @var $application AbstractHttpApplication
         */
        $application = $this->getMockForAbstractClass(AbstractHttpApplication::class);
        
        $eventsHandler = $application->getEventsHandler();
        
        $this->assertInstanceOf(EventsHandler::class, $eventsHandler);
    }
    
    public function testDefaultServicesFactoryIsInstantiatedOnGet()
    {
        /**
         * @var $application AbstractHttpApplication
         */
        $application = $this->getMockForAbstractClass(AbstractHttpApplication::class);
        
        $servicesFactory = $application->getServicesFactory();
        
        $this->assertInstanceOf(ServicesFactory::class, $servicesFactory);
    }
    
    public function testRun()
    {
        /**
         * @var $application AbstractHttpApplication
         */
        $application = $this->getMockForAbstractClass(AbstractHttpApplication::class);
        
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
        /** @var AbstractHttpApplication $app */
        $app = $this->getMockForAbstractClass(AbstractHttpApplication::class);
        
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
        $app = new class extends AbstractHttpApplication {
            public function init() {}
        };
        $app->setParam('test.param', 'test.value');

        $this->assertEquals('test.value', $app->getParam('test.param'));
        $this->assertEquals('test.value', $app->getConfig()->subset(Param::class)->get('test.param'));
    }

    public function testParamsAreFetchedFromConfig()
    {
        $app = $this->getMockForAbstractClass(AbstractHttpApplication::class);
        $app->getConfig()->import(new Param('test.param', 'test.value'));

        $this->assertEquals('test.value', $app->getParam('test.param'));
    }
}

