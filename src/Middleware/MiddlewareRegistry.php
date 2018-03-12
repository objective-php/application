<?php
/**
 * Created by PhpStorm.
 * User: gde
 * Date: 12/03/2018
 * Time: 19:02
 */

namespace ObjectivePHP\Application\Middleware;


use ObjectivePHP\Application\ApplicationAwareInterface;
use ObjectivePHP\Application\Filter\FiltersProviderInterface;
use ObjectivePHP\Primitives\Collection\Collection;
use Psr\Http\Server\MiddlewareInterface;

class MiddlewareRegistry extends Collection implements ApplicationAwareInterface
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
        $this->restrictTo(MiddlewareInterface::class);

        parent::__construct($input);
    }

    public function register(MiddlewareInterface $middleware, $position = null)
    {
        $position = $position ?? $this->getDefaultInsertionPosition();

        switch($position)
        {
            case self::LAST:
                $this->append($middleware);
                break;

            // TODO handle other insertion positions
        }
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
    }

    /**
     * @return MiddlewareInterface|null
     */
    protected function getNextMiddleware()
    {
        while ($middleware = $this->current()) {

            $this->next();
            // filter step

            if (($middleware instanceof FiltersProviderInterface) && !$middleware->runFilters()) {
                continue;
            }

            return $middleware;
        }

    }



}