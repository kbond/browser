<?php

namespace Zenstruck\Browser\Dom\Node\Form\Field;

use Zenstruck\Browser\Dom\Node\Form\Field;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class Input extends Field
{
    public function type(): string
    {
        return $this->attributes()->get('type') ?? 'text';
    }

    public function value(): ?string
    {
        return $this->attributes()->get('value');
    }
}
