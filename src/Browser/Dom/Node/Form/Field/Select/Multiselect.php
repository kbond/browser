<?php

namespace Zenstruck\Browser\Dom\Node\Form\Field\Select;

use Zenstruck\Browser\Dom\Node\Form\Field\Select;
use Zenstruck\Browser\Dom\Nodes;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class Multiselect extends Select
{
    public function selectedOptions(): Nodes
    {
        return $this->availableOptions()->filter('[selected]');
    }

    /**
     * @return string[]
     */
    public function selectedValues(): array
    {
        return \array_filter($this->selectedOptions()->map(fn(Option $option) => $option->value()));
    }

    /**
     * @return string[]
     */
    public function value(): array
    {
        return $this->selectedValues();
    }
}
