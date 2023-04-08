<?php

namespace Zenstruck\Browser\Dom\Node\Form\Field;

use Zenstruck\Browser\Dom\Node\Form\Field;
use Zenstruck\Browser\Dom\Node\Form\Field\Select\Option;
use Zenstruck\Browser\Dom\Nodes;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
abstract class Select extends Field
{
    public function availableOptions(): Nodes
    {
        return $this->descendents('option');
    }

    /**
     * @return string[]
     */
    public function availableValues(): array
    {
        return \array_filter($this->availableOptions()->map(fn(Option $option) => $option->value()));
    }

    public function isMultiple(): bool
    {
        return $this->attributes()->has('multiple');
    }
}
