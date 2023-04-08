<?php

namespace Zenstruck\Browser\Dom\Node\Form;

use Zenstruck\Browser\Dom\Node\FormAware;
use Zenstruck\Browser\Dom\Selector;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
abstract class Field extends FormAware
{
    public function label(): ?Label
    {
        $id = $this->attributes()->get('id');

        if ($id && $label = $this->form()?->descendent(Selector::css(\sprintf('label[for="%s"]', $id)))) {
            return $label->ensure(Label::class);
        }

        // check if wrapped in a label
        return $this->ancestor('label')?->ensure(Label::class);
    }

    abstract public function value(): mixed;
}
