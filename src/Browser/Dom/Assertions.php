<?php

namespace Zenstruck\Browser\Dom;

use Zenstruck\Assert;
use Zenstruck\Browser\Dom;
use Zenstruck\Browser\Dom\Node\Form\Field\Input;
use Zenstruck\Browser\Dom\Node\Form\Field\Input\Checkbox;
use Zenstruck\Browser\Dom\Node\Form\Field\Input\Radio;
use Zenstruck\Browser\Dom\Node\Form\Field\Select\Combobox;
use Zenstruck\Browser\Dom\Node\Form\Field\Select\Multiselect;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
trait Assertions
{
    public function contains(string $expected): static
    {
        Assert::that($this->dom()->crawler()->text())->contains($expected);

        return $this;
    }

    public function doesNotContain(string $expected): static
    {
        Assert::that($this->dom()->crawler()->text())->doesNotContain($expected);

        return $this;
    }

    public function containsIn(string $selector, string $expected): static
    {
        Assert::that($this->node($selector)->text())->contains($expected);

        return $this;
    }

    public function doesNotContainIn(string $selector, string $expected): static
    {
        Assert::that($this->node($selector)->text())->doesNotContain($expected);

        return $this;
    }

    public function hasElement(string $selector): static
    {
        Assert::that($this->dom()->find($selector))->isNotNull('Element with selector "{selector}" does not exist.', ['selector' => $selector]);

        return $this;
    }

    public function doesNotHaveElement(string $selector): static
    {
        Assert::that($this->dom()->find($selector))->isNull('Element with selector "{selector}" exists but it should not.', ['selector' => $selector]);

        return $this;
    }

    public function hasElementCount(string $selector, int $count): static
    {
        Assert::that($this->dom()->findAll($selector))->hasCount($count, 'Expected {expected} elements with selector "{selector}" but found {actual}.', ['selector' => $selector]);

        return $this;
    }

    public function attributeContains(string $selector, string $attribute, string $expected): static
    {
        Assert::that($this->node($selector)->attributes()->get($attribute))
            ->isNotNull('Element with selector "{selector}" does not have attribute "{attribute}".', ['selector' => $selector, 'attribute' => $attribute])
            ->contains($expected, 'Element with selector "{selector}" attribute "{attribute}" does not contain "{expected}".', ['selector' => $selector, 'attribute' => $attribute, 'expected' => $expected])
        ;

        return $this;
    }

    public function attributeDoesNotContain(string $selector, string $attribute, string $expected): static
    {
        Assert::that($this->node($selector)->attributes()->get($attribute))
            ->doesNotContain($expected, 'Element with selector "{selector}" attribute "{attribute}" contains "{expected}" but it should not.', ['selector' => $selector, 'attribute' => $attribute, 'expected' => $expected])
        ;

        return $this;
    }

    public function fieldEquals(string $selector, string $expected): static
    {
        Assert::that($this->node($selector, Input::class)->value())
            ->equals($expected, 'Field with selector "{selector}" does not equal "{expected}".', ['selector' => $selector])
        ;

        return $this;
    }

    public function fieldDoesNotEqual(string $selector, string $expected): static
    {
        Assert::that($this->node($selector, Input::class)->value())
            ->isNotEqualTo($expected, 'Field with selector "{selector}" equals "{expected}" but it should not.', ['selector' => $selector])
        ;

        return $this;
    }

    public function fieldSelected(string $selector, string $expected): static
    {
        $node = $this->node($selector);

        switch ($node::class) {
            case Radio::class:
                Assert::that($node->value())
                    ->equals($expected, 'Radio with selector "{selector}" does not equal "{expected}".', ['selector' => $selector])
                    ->and($node->isSelected())
                    ->is(true, 'Radio with selector "{selector}" is not selected.', ['selector' => $selector])
                ;

                break;

            case Multiselect::class:
                Assert::that($node->selectedValues())
                    ->contains($expected, 'Multiselect with selector "{selector}" does not have "{expected}" selected.', ['selector' => $selector])
                ;

                break;

            case Combobox::class:
                Assert::that($node->selectedValue())
                    ->is($expected, 'Combobox with selector "{selector}" has "{actual}" selected but expected "{expected}".', ['selector' => $selector])
                ;

                break;

            default:
                Assert::fail('Field with selector "{selector}" is not a radio, multiselect, or combobox.', ['selector' => $selector]);
        }

        return $this;
    }

    public function fieldNotSelected(string $selector, string $expected): static
    {
        $node = $this->node($selector);

        switch ($node::class) {
            case Radio::class:
                Assert::that($node->isSelected())
                    ->is(false, 'Radio with selector "{selector}" is selected but it should not be.', ['selector' => $selector])
                ;

                break;

            case Multiselect::class:
                Assert::that($node->selectedValues())
                    ->doesNotContain($expected, 'Multiselect with selector "{selector}" has "{expected}" selected but it should not.', ['selector' => $selector])
                ;

                break;

            case Combobox::class:
                Assert::that($node->selectedValue())
                    ->isNot($expected, 'Combobox with selector "{selector}" has "{expected}" selected but it should not.', ['selector' => $selector])
                ;

                break;

            default:
                Assert::fail('Field with selector "{selector}" is not a radio, multiselect, or combobox.', ['selector' => $selector]);
        }

        return $this;
    }

    public function fieldChecked(string $selector): static
    {
        $node = $this->node($selector);

        if ($node instanceof Checkbox) {
            Assert::that($node->isChecked())
                ->is(true, 'Checkbox with selector "{selector}" is not checked.', ['selector' => $selector])
            ;
        } elseif ($node instanceof Radio) {
            Assert::that($node->isSelected())
                ->is(true, 'Radio with selector "{selector}" is not selected.', ['selector' => $selector])
            ;
        } else {
            Assert::fail('Field with selector "{selector}" is not a checkbox or radio.', ['selector' => $selector]);
        }

        return $this;
    }

    public function fieldNotChecked(string $selector): static
    {
        $node = $this->node($selector);

        if ($node instanceof Checkbox) {
            Assert::that($node->isChecked())
                ->is(false, 'Checkbox with selector "{selector}" is checked but it should not be.', ['selector' => $selector])
            ;
        } elseif ($node instanceof Radio) {
            Assert::that($node->isSelected())
                ->is(true, 'Radio with selector "{selector}" is selected but it should not be.', ['selector' => $selector])
            ;
        } else {
            Assert::fail('Field with selector "{selector}" is not a checkbox or radio.', ['selector' => $selector]);
        }

        return $this;
    }

    /**
     * @template N as Node
     *
     * @param class-string<N> $type
     *
     * @return N
     */
    private function node(string $selector, string $type = Node::class): Node
    {
        if (!$node = $this->dom()->find($selector)) {
            Assert::fail('Could not find node with selector "{selector}".', ['selector' => $selector]);
        }

        return Assert::try(static fn() => $node->ensure($type));
    }

    abstract private function dom(): Dom;
}
