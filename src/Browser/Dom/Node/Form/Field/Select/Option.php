<?php

namespace Zenstruck\Browser\Dom\Node\Form\Field\Select;

use Zenstruck\Browser\Dom\Node\Form\Field;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class Option extends Field
{
    public function value(): ?string
    {
        return $this->attributes()->get('value') ?? $this->text();
    }

    public function isSelected(): bool
    {
        return $this->attributes()->has('selected');
    }
}
