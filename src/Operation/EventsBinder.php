<?php
namespace ObjectivePHP\Application\Operation;


use ObjectivePHP\Application\ApplicationInterface;
use ObjectivePHP\Application\Middleware\AbstractMiddleware;
use ObjectivePHP\Application\Config\Event;
/**
 * Class EventsBinder
 *
 * @package ObjectivePHP\Application\Operation
 */
class EventsBinder extends AbstractMiddleware
{
    /**
     * @param ApplicationInterface $app
     */
    public function run(ApplicationInterface $app)
    {
        $this->setApplication($app);

        $events = $app->getConfig()->get(Event::class, []);

        $eventsHandler = $app->getEventsHandler();

        /** @var Event $event */
        foreach ($events as $event) {
            $eventsHandler->bind(
                $event->getEvent(),
                $event->getCallback(),
                $event->getMode()
            );
        }
    }
}
