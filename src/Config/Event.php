<?php
namespace ObjectivePHP\Application\Config;

use ObjectivePHP\Config\Exception;
use ObjectivePHP\Config\StackedValuesDirective;
use ObjectivePHP\Events\EventsHandler;
use ObjectivePHP\Config\DirectiveInterface;
use ObjectivePHP\Config\ConfigInterface;
use ObjectivePHP\ServicesFactory\ServiceReference;

/**
 * Bin an event in the events-handler
 *
 * @package ObjectivePHP\Application\Config
 */
class Event extends StackedValuesDirective
{
    /** @var string */
    protected $event;

    /** @var mixed */
    protected $callback;

    /** @var string */
    protected $mode = EventsHandler::BINDING_MODE_LAST;

    public function __construct(string $eventName, $callback, string $mode = EventsHandler::BINDING_MODE_LAST)
    {
        $this->setEvent($eventName);
        $this->setCallback($callback);
        $this->setMode($mode);

        parent::__construct($this);
    }

    /**
     * Get Event
     *
     * @return string
     */
    public function getEvent(): string
    {
        return $this->event;
    }

    /**
     * Set Event
     *
     * @param string $event
     *
     * @return Event
     */
    public function setEvent(string $event): Event
    {
        $this->event = $event;

        return $this;
    }

    /**
     * Get Callback
     *
     * @return mixed
     */
    public function getCallback()
    {
        return $this->callback;
    }

    /**
     * Set Callback
     *
     * @param mixed $callback
     *
     * @return Event
     */
    public function setCallback($callback): Event
    {
        $this->callback = $callback;

        return $this;
    }

    /**
     * Get Mode
     *
     * @return string
     */
    public function getMode(): string
    {
        return $this->mode;
    }

    /**
     * Set Mode
     *
     * @param string $mode
     *
     * @return Event
     */
    public function setMode(string $mode): Event
    {
        $this->mode = $mode;

        return $this;
    }
}
