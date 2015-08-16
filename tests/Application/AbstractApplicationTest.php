<?php
    use ObjectivePHP\Application\AbstractApplication;
    use ObjectivePHP\Events\EventsHandler;
    use ObjectivePHP\PHPUnit\TestCase;
    use ObjectivePHP\ServicesFactory\ServicesFactory;
    

    class AbstractApplicationTest extends TestCase
    {


        public function testDefaultEventsHandlerIsInstantiatedOnGet()
        {

            $application = $this->getMockForAbstractClass(AbstractApplication::class);

            $eventsHandler = $application->getEventsHandler();

            $this->assertInstanceOf(EventsHandler::class, $eventsHandler);
        }

        public function testDefaultServicesFactoryIsInstantiatedOnGet()
        {

            $application = $this->getMockForAbstractClass(AbstractApplication::class);

            $servicesFactory = $application->getServicesFactory();

            $this->assertInstanceOf(ServicesFactory::class, $servicesFactory);
        }
    }

