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

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @implements \IteratorAggregate<Node>
 */
final class Nodes implements \IteratorAggregate, \Countable
{
    private Crawler $crawler;

    private function __construct(?Crawler $crawler = null)
    {
        $this->crawler = $crawler ?? new Crawler();
    }

    public static function create(?Crawler $crawler = null): self
    {
        return new self($crawler);
    }

    public function crawler(): Crawler
    {
        return $this->crawler;
    }

    public function first(?string $selector = null): ?Node
    {
        if ($selector) {
            return $this->filter($selector)->first();
        }

        return $this->count() ? Node::create($this->crawler->first()) : null;
    }

    public function last(): ?Node
    {
        return $this->count() ? Node::create($this->crawler->last()) : null;
    }

    public function filter(string $selector): self
    {
        return self::create(Selector::wrap($selector)->filter($this->crawler));
    }

    /**
     * @template Input of Node
     * @template Return
     *
     * @param callable(Input):Return $callback
     *
     * @return Return[]
     */
    public function map(callable $callback): array
    {
        return \array_map($callback, \iterator_to_array($this)); // @phpstan-ignore-line
    }

    public function text(): ?string
    {
        try {
            return $this->crawler->text();
        } catch (\InvalidArgumentException) {
            return null;
        }
    }

    public function html(): ?string
    {
        try {
            return $this->crawler->html();
        } catch (\InvalidArgumentException) {
            return null;
        }
    }

    public function getIterator(): \Traversable
    {
        for ($i = 0; $i < $this->count(); ++$i) {
            yield Node::create($this->crawler->eq($i));
        }
    }

    public function count(): int
    {
        return \count($this->crawler);
    }

    public function dump(): self
    {
        foreach ($this as $node) {
            $node->dump();
        }

        return $this;
    }

    public function dd(): void
    {
        $this->dump();

        exit(1);
    }
}
