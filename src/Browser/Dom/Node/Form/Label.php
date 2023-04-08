<?php

namespace Zenstruck\Browser\Dom\Node\Form;

use Zenstruck\Browser\Dom\Node\FormAware;
use Zenstruck\Browser\Dom\Selector;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class Label extends FormAware
{
    public function field(): ?Field
    {
        if ($for = $this->attributes()->get('for')) {
            return $this->form()?->descendents(Selector::id($for))->first()?->ensure(Field::class);
        }

        // check if wrapping field
        return $this->descendents(Field::class)->first()?->ensure(Field::class);
    }
}
