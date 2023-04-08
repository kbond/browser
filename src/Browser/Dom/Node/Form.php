<?php

namespace Zenstruck\Browser\Dom\Node;

use Zenstruck\Browser\Dom\Node;
use Zenstruck\Browser\Dom\Node\Form\Button;
use Zenstruck\Browser\Dom\Nodes;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class Form extends Node
{
    public function fields(string $selector = '[name]'): Nodes
    {
        return $this->descendents($selector);
    }

    public function buttons(): Nodes
    {
        return $this->descendents('button,input[type="button"],input[type="submit"],input[type="reset"]');
    }

    public function submitButtons(): Nodes
    {
        return $this->descendents('input[type="submit"],button[type="submit"]');
    }

    public function submitButton(): ?Button
    {
        return $this->submitButtons()->first()?->ensure(Button::class);
    }
}
