<?php

namespace Zenstruck\Browser\Dom\Node\Form\Field\Select;

use Zenstruck\Browser\Dom\Node\Form\Field\Select;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class Combobox extends Select
{
    public function selectedOption(): ?Option
    {
        foreach ($this->availableOptions() as $option) {
            // hack for panther
            $option = $option->ensure(Option::class);

            if ($option->isSelected()) {
                return $option;
            }
        }

        return null;
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
