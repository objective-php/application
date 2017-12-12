<?php

namespace Test\ObjectivePHP\Application\Middleware;

use ObjectivePHP\Application\AbstractApplication;
use ObjectivePHP\Application\Middleware\AbstractWorkflowFiltersProvider;
use PHPUnit\Framework\TestCase;

/**
 * Class AbstractEmbeddedFiltersMiddlewareTest
 *
 * @package Test\ObjectivePHP\Application\Middleware
 */
class AbstractEmbeddedFiltersMiddlewareTest extends TestCase
{
    public function testRunFilterIsExecuted()
    {
        /** @var AbstractApplication $app */
        $app = $this->getMockForAbstractClass(AbstractApplication::class);

        /** @var AbstractWorkflowFiltersProvider $middleware */
        $middleware = $this->getMockForAbstractClass(AbstractWorkflowFiltersProvider::class);
        $middleware->setApplication($app);

        $i = 0;
        $mApp = null;
        $middleware->addFilter(function ($app) use (&$i, &$mApp) {
            $mApp = $app;
            $i++;

            return false;
        });

        $this->assertFalse($middleware->runFilters());
        $this->assertEquals($app, $mApp);
        $this->assertEquals(1, $i);
    }
}
