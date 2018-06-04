<?php

namespace ObjectivePHP\Application\Middleware;

use ObjectivePHP\Filter\FiltersProviderInterface;
use ObjectivePHP\Primitives\Collection\Collection;
use Psr\Http\Server\MiddlewareInterface;

/**
 * Class MiddlewareRegistry
 *
 * @package ObjectivePHP\Application\Middleware
 */
class MiddlewareRegistry extends Collection
{
    const LAST = 'last';
    const BEFORE_LAST = 'before_last';
    const FIRST = 'first';
    const CURRENT = 'current';

    protected $defaultInsertionPosition = self::LAST;

    /**
     * MiddlewareRegistry constructor.
     *
     * @param array $input
     *
     * @throws \ObjectivePHP\Primitives\Exception
     */
    public function __construct(array $input = [])
    {
        parent::__construct($input);
        $this->restrictTo(MiddlewareInterface::class);
    }

    public function registerMiddleware(MiddlewareInterface $middleware, $position = null)
    {
        $position = $position ?? $this->getDefaultInsertionPosition();

        switch ($position) {
            case self::LAST:
                $this->append($middleware);
                break;

            case self::BEFORE_LAST:
                $middlewares = $this->getInternalValue();
                $last = array_pop($middlewares);
                $middlewares[] = $middleware;
                $middlewares[] = $last;
                $this->setInternalValue(array_filter($middlewares));
                break;

            case self::FIRST:
                $this->prepend($middleware);
                break;

            case self::CURRENT:
                $this->set($this->key(), $middleware);
                break;
        }

        return $this;
    }

    /**
     * @return MiddlewareInterface|null
     */
    public function getNextMiddleware()
    {
        while ($middleware = $this->current()) {
            $this->next();
            // filter step

            if (($middleware instanceof FiltersProviderInterface) && !$middleware->getFilterEngine()->run()) {
                continue;
            }

            return $middleware;
        }
    }

    public function getDefaultInsertionPosition(): string
    {
        return $this->defaultInsertionPosition;
    }

    public function setDefaultInsertionPosition(string $defaultInsertionPosition)
    {
        $this->defaultInsertionPosition = $defaultInsertionPosition;

        return $this;
    }
}
