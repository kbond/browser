<?php

namespace Zenstruck\Browser\Dom\Node\Form\Field\Input;

use Zenstruck\Browser\Dom\Node\Form\Field\Input;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class Radio extends Input
{
    public function isSelected(): bool
    {
        return $this->attributes()->has('checked');
    }
}
