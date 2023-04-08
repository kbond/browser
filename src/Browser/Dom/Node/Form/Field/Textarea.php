<?php

namespace Zenstruck\Browser\Dom\Node\Form\Field;

use Zenstruck\Browser\Dom\Node\Form\Field;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class Textarea extends Field
{
    public function value(): ?string
    {
        return $this->directText();
    }
}
