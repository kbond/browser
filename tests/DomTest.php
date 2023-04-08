<?php

namespace Zenstruck\Browser\Tests;

use PHPUnit\Framework\TestCase;
use Zenstruck\Browser\Dom;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class DomTest extends TestCase
{
    private Dom $dom;

    protected function setUp(): void
    {
        $this->dom = new Dom(\file_get_contents(__DIR__.'/Fixture/files/page1.html'));
    }

    /**
     * @test
     */
    public function find_input_fields(): void
    {
        $this->assertSame('input 1', $this->dom->find('input')?->value());
        $this->assertSame('input 1', $this->dom->find('input1')?->value());
        $this->assertSame('input 1', $this->dom->find('#input1')?->value());
        $this->assertSame('input 1', $this->dom->find('input_1')?->value());
        $this->assertSame('input 1', $this->dom->find('Input 1')?->value());
    }
}
