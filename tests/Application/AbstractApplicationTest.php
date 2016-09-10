<?php
use ObjectivePHP\Application\AbstractApplication;
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
}

