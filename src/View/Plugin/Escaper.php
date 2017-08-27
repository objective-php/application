<?php
namespace ObjectivePHP\Application\View\Plugin;

/**
 * Escape a string into a secure html string to protect it from xss
 *
 * @package ObjectivePHP\Application\View\Plugin
 */
class Escaper extends AbstractPlugin
{
    const DEFAULT_FLAGS = ENT_QUOTES | ENT_SUBSTITUTE;

    /** @inheritdoc */
    public function __invoke(...$args)
    {
        $str = $args[0] ?? null;
        $flags = $args[1] ?? self::DEFAULT_FLAGS;

        if (null !== $str) {
            $str = htmlspecialchars($str, $flags);
        }

        return $str;
    }
}