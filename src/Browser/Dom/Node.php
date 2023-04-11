<?php

/*
 * This file is part of the zenstruck/browser package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Browser\Dom;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Panther\DomCrawler\Crawler as PantherCrawler;
use Zenstruck\Browser\Dom\Exception\RuntimeException;
use Zenstruck\Browser\Dom\Node\Attributes;
use Zenstruck\Browser\Dom\Node\Form;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class Node
{
    private function __construct(private Crawler $crawler)
    {
    }

    public static function create(Crawler $crawler): self
    {
        $node = new self($crawler);

        return match(true) {
            $node->tagIs('form') => new Form($crawler),
            $node->tagIs('label') => new Form\Label($crawler),
            $node->tagIs('textarea') => new Form\Field\Textarea($crawler),
            $node->tagIs('input') && $node->attributes()->is('type', 'checkbox') => new Form\Field\Input\Checkbox($crawler),
            $node->tagIs('input') && $node->attributes()->is('type', 'radio') => new Form\Field\Input\Radio($crawler),
            $node->tagIs('input') && $node->attributes()->is('type', 'input', 'button', 'reset') => new Form\Button($crawler),
            $node->tagIs('button') => new Form\Button($crawler),
            $node->tagIs('input') => new Form\Field\Input($crawler),
            $node->tagIs('option') => new Form\Field\Select\Option($crawler),
            $node->tagIs('select') && $node->attributes()->has('multiple') => new Form\Field\Select\Multiselect($crawler),
            $node->tagIs('select') => new Form\Field\Select\Combobox($crawler),
            default => $node,
        };
    }

    public function crawler(): Crawler
    {
        return $this->crawler;
    }

    public function element(): \DOMElement
    {
        $element = $this->normalizedCrawler()->getNode(0);

        if (!$element instanceof \DOMElement) {
            throw new RuntimeException('Unable to get attributes from non-element node.');
        }

        return $element;
    }

    public function tag(): string
    {
        return $this->crawler->nodeName();
    }

    public function tagIs(string $expected): bool
    {
        return \strtolower($expected) === \strtolower($this->tag());
    }

    public function attributes(): Attributes
    {
        return new Attributes($this->element());
    }

    public function text(): string
    {
        return $this->crawler->text();
    }

    public function directText(): ?string
    {
        return $this->crawler->innerText();
    }

    public function html(): ?string
    {
        if ('' === $html = $this->crawler->outerHtml()) {
            return null;
        }

        return $html;
    }

    public function innerHtml(): string
    {
        return $this->crawler->html();
    }

    public function parent(): ?self
    {
        return Nodes::create($this->crawler->ancestors())->first();
    }

    public function next(): ?self
    {
        return Nodes::create($this->crawler->nextAll())->first();
    }

    public function previous(): ?self
    {
        return Nodes::create($this->crawler->previousAll())->first();
    }

    public function closest(string $selector): ?self
    {
        $closest = $this->crawler->closest($selector);

        return $closest ? self::create($closest) : null;
    }

    public function ancestor(string $selector): ?Node
    {
        return $this->ancestors($selector)->first();
    }

    public function ancestors(?string $selector = null): Nodes
    {
        return self::applySelectorTo($this->crawler->ancestors(), $selector);
    }

    public function siblings(?string $selector = null): Nodes
    {
        return self::applySelectorTo($this->crawler->siblings(), $selector);
    }

    public function children(?string $selector = null): Nodes
    {
        return self::applySelectorTo($this->crawler->children(), $selector);
    }

    public function descendent(string $selector): ?Node
    {
        return $this->descendents($selector)->first();
    }

    public function descendents(?string $selector = null): Nodes
    {
        if ($this->crawler instanceof PantherCrawler) {
            return self::applySelectorTo($this->crawler, $selector ?? '*');
        }

        // todo can this be improved?
        $crawler = $this->crawler->filter('*')->reduce(static function(Crawler $node, int $i) {
            return 0 !== $i;
        });

        return self::applySelectorTo($crawler, $selector);
    }

    /**
     * @template T of self
     *
     * @param class-string<T> $type
     */
    public function is(string $type): bool
    {
        return $this instanceof $type;
    }

    /**
     * @template T of self
     *
     * @param class-string<T> $type
     *
     * @return T
     */
    public function ensure(string $type): self
    {
        if ($this instanceof $type) {
            return $this;
        }

        throw new RuntimeException(\sprintf('Expected "%s", got "%s".', $type, $this::class));
    }

    public function id(): ?string
    {
        return $this->attributes()->get('id');
    }

    public function dump(): static
    {
        \function_exists('dump') ? dump($this->html()) : \var_dump($this->html());

        return $this;
    }

    public function isVisible(): bool
    {
        if ($this->crawler instanceof PantherCrawler) {
            return $this->crawler->isDisplayed();
        }

        return true;
    }

    public function dd(): void
    {
        $this->dump();

        exit(1);
    }

    private static function applySelectorTo(Crawler $crawler, ?string $selector = null): Nodes
    {
        $nodes = Nodes::create($crawler);

        return $selector ? $nodes->filter($selector) : $nodes;
    }

    private function normalizedCrawler(): Crawler
    {
        if ($this->crawler instanceof PantherCrawler) {
            return (new Crawler($this->crawler->html()))->filter($this->tag());
        }

        return $this->crawler;
    }
}
