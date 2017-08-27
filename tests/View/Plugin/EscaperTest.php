<?php

use ObjectivePHP\Application\View\Plugin\Escaper;
use PHPUnit\Framework\TestCase;

class EscaperTest extends TestCase
{
    public function testIsCallable()
    {
        $escaper = new Escaper();

        $this->assertTrue(is_callable($escaper));
    }

    public function testInvoke()
    {
        $escaper = new Escaper();

        $this->assertEquals('&lt;i&gt;"string"&lt;/i&gt;', $escaper('<i>"string"</i>', ENT_SUBSTITUTE));
        $this->assertEquals('&lt;i&gt;&quot;string&quot;&lt;/i&gt;', $escaper('<i>"string"</i>', ENT_SUBSTITUTE | ENT_QUOTES));
        $this->assertEquals('&lt;i&gt;&quot;string&quot;&lt;/i&gt;', $escaper('<i>"string"</i>'));
        $this->assertEquals('&lt;i&gt;string&lt;/i&gt;', $escaper('<i>string</i>'));
    }
}
