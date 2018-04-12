<?php
/**
 * Created by PhpStorm.
 * User: gde
 * Date: 12/03/2018
 * Time: 19:02
 */

namespace ObjectivePHP\Application\Middleware;


use ObjectivePHP\Filter\FiltersProviderInterface;
use ObjectivePHP\Primitives\Collection\Collection;
use Psr\Http\Server\MiddlewareInterface;

class MiddlewareRegistry extends Collection
{

    const LAST = 'last';
    const BEFORE_LAST = 'before_last';
    const FIRST = 'first';
    const CURRENT = 'current';

    protected $defaultInsertionPosition = self::LAST;

    /**
     * @param array $input
     *
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
                
        }
        
        return $this;
    }

    /**
     * @return string
     */
    public function getDefaultInsertionPosition(): string
    {
        return $this->defaultInsertionPosition;
    }

    /**
     * @param string $defaultInsertionPosition
     */
    public function setDefaultInsertionPosition(string $defaultInsertionPosition)
    {
        $this->defaultInsertionPosition = $defaultInsertionPosition;

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


}
