<?php

namespace Zenstruck\Browser\Dom\Node\Form;

use Zenstruck\Browser\Dom\Node\FormAware;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class Button extends FormAware
{
    public function type(): string
    {
        return $this->attributes()->get('type') ?? 'button';
    }
}
