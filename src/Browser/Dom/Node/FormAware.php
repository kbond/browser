<?php

namespace Zenstruck\Browser\Dom\Node;

use Zenstruck\Browser\Dom\Node;
use Zenstruck\Browser\Dom\Nodes;
use Zenstruck\Browser\Dom\Selector;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
abstract class FormAware extends Node
{
    public function name(): ?string
    {
        return $this->attributes()->get('name');
    }

    public function collection(): Nodes
    {
        if (!$name = $this->name()) {
            return Nodes::create();
        }

        return $this->form()?->descendents(Selector::field($name)) ?? Nodes::create();
    }

    public function form(): ?Form
    {
        return $this->ancestor('form')?->ensure(Form::class);
    }

    public function isDisabled(): bool
    {
        return $this->attributes()->has('disabled');
    }
}
