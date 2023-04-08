<?php

namespace Zenstruck\Browser\Dom\Node\Form\Field\Select;

use Zenstruck\Browser\Dom\Node\Form\Field\Select;
use Zenstruck\Browser\Dom\Node\Form\Field\Select\Option;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class Combobox extends Select
{
    public function selectedOption(): ?Option
    {
        return $this->availableOptions()->first('[selected]')?->ensure(Option::class);
    }

    public function selectedValue(): ?string
    {
        return $this->selectedOption()?->value();
    }

    public function value(): ?string
    {
        return $this->selectedValue();
    }
}
