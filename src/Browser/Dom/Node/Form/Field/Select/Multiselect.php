<?php

namespace Zenstruck\Browser\Dom\Node\Form\Field\Select;

use Symfony\Component\Panther\DomCrawler\Crawler as PantherCrawler;
use Zenstruck\Browser\Dom\Node\Form\Field\Select;
use Zenstruck\Browser\Dom\Nodes;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class Multiselect extends Select
{
    public function selectedOptions(): Nodes
    {
        if ($this->crawler() instanceof PantherCrawler) {
            return $this->descendents('option:checked');
        }

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
