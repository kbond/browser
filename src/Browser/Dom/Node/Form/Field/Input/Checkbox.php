<?php

namespace Zenstruck\Browser\Dom\Node\Form\Field\Input;

use Zenstruck\Browser\Dom\Node\Form\Field\Input;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class Checkbox extends Input
{
    public function isChecked(): bool
    {
        return $this->attributes()->has('checked');
    }

    public function value(): ?string
    {
        return parent::value() ?? ($this->isChecked() ? 'on' : null);
    }
}
