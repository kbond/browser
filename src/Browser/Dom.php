<?php

/*
 * This file is part of the zenstruck/browser package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Browser;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Panther\DomCrawler\Crawler as PantherCrawler;
use Zenstruck\Browser\Dom\Node;
use Zenstruck\Browser\Dom\Nodes;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class Dom
{
    private Crawler $crawler;

    public function __construct(string|Crawler $crawler)
    {
        if (\is_string($crawler)) {
            $crawler = new Crawler($crawler);
        }

        $this->crawler = $crawler;
    }

    public function find(string $selector): ?Node
    {
        if ($this->crawler instanceof PantherCrawler && 'html' === $selector) {
            return Node::create($this->crawler);
        }

        return Nodes::create($this->crawler)->first($selector);
    }

    public function findAll(string $selector): Nodes
    {
        return Nodes::create($this->crawler)->filter($selector);
    }

    public function crawler(): Crawler
    {
        return $this->crawler;
    }

    public function dump(?string $selector = null): static
    {
        $dump = static fn(mixed $what) => \function_exists('dump') ? dump($what) : \var_dump($what);

        null === $selector ? $dump($this->crawler->outerHtml()) : $this->findAll($selector)->dump();

        return $this;
    }

    public function dd(?string $selector = null): void
    {
        $this->dump($selector);

        exit(1);
    }
}
