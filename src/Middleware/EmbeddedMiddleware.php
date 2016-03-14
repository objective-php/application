<?php

namespace ObjectivePHP\Application\Middleware;


use ObjectivePHP\Application\ApplicationInterface;
use ObjectivePHP\Invokable\Invokable;
use ObjectivePHP\Invokable\InvokableInterface;

/**
 * Class EmbeddedMiddleware
 *
 * @package ObjectivePHP\Application\Hook
 */
class EmbeddedMiddleware extends AbstractMiddleware
{

    /**
     * @var
     */
    protected $invokable;

    /**
     * EmbeddedMiddleware constructor.
     *
     * @param $invokable
     */
    public function __construct($invokable)
    {
        $this->invokable = Invokable::cast($invokable);
    }

    /**
     * @param ApplicationInterface $app
     *
     * @return mixed
     */
    public function run(ApplicationInterface $app)
    {
        $invokable = $this->getInvokable()->setApplication($app);

        return $invokable($app);
    }

    /**
     * @return InvokableInterface
     */
    public function getInvokable()
    {
        return $this->invokable;
    }

    /**
     * @return string
     */
    public function getDescription() : string
    {
        return 'Middleware embedding ' . $this->getInvokable()->getDescription();
    }
}
