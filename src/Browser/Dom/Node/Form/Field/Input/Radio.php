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

    public function selected(): ?Radio
    {
        foreach ($this->collection() as $radio) {
            // hack for panther
            $radio = $radio->ensure(self::class);

            if ($radio->isSelected()) {
                return $radio;
            }
        }

        return null;
    }

    public function selectedValue(): ?string
    {
        return $this->selected()?->value();
    }
}
