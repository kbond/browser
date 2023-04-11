<?php

namespace Zenstruck\Browser\Dom;

use Symfony\Component\CssSelector\Exception\SyntaxErrorException;
use Symfony\Component\DomCrawler\Crawler;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class Selector implements \Stringable
{
    private const SEPARATOR = '__:__';
    private const SEPARATOR_FORMAT = '%s'.self::SEPARATOR.'%s';
    private const CSS = 'css';
    private const ID = 'id';
    private const FIELD = 'field';
    private const BUTTON = 'button';
    private const LINK = 'link';
    private const IMAGE = 'image';

    private function __construct(private string $value)
    {
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public static function wrap(string $value): self
    {
        return new self($value);
    }

    public static function css(string $value): self
    {
        return self::create(self::CSS, $value);
    }

    public static function id(string $value): self
    {
        return self::create(self::ID, $value);
    }

    public static function field(string $value): self
    {
        return self::create(self::FIELD, $value);
    }

    public static function button(string $value): self
    {
        return self::create(self::BUTTON, $value);
    }

    public static function link(string $value): self
    {
        return self::create(self::LINK, $value);
    }

    public static function image(string $value): self
    {
        return self::create(self::IMAGE, $value);
    }

    public function filter(Crawler $crawler): Crawler
    {
        return self::filterByType($crawler, ...$this->parse());
    }

    public static function filterByType(Crawler $crawler, ?string $type, string $value): Crawler
    {
        return match($type) {
            self::CSS => $crawler->filter($value),
            self::ID => $crawler->filter(\sprintf('#%s', \ltrim($value, '#'))),
            self::FIELD => self::filterField($crawler, $value),
            self::LINK => $crawler->selectLink($value),
            self::BUTTON => $crawler->selectButton($value),
            self::IMAGE => $crawler->selectImage($value),
            default => self::autoFilter($crawler, $value),
        };
    }

    private static function autoFilter(Crawler $crawler, string $value): Crawler
    {
        if (\count($filtered = self::filterField($crawler, $value))) {
            return $filtered;
        }

        foreach ([self::IMAGE, self::BUTTON, self::LINK, self::CSS] as $type) {
            try {
                if (\count($filtered = self::filterByType($crawler, $type, $value))) {
                    return $filtered;
                }
            } catch (SyntaxErrorException) {
                // ignore
            }
        }

        return new Crawler();
    }

    private static function filterField(Crawler $crawler, string $value): Crawler
    {
        try {
            if (\count($filtered = self::filterByType($crawler, self::ID, $value))) {
                return $filtered;
            }
        } catch (\Throwable) {}

        try {
            if (\count($filtered = $crawler->filter(\sprintf('[name="%s"]', $value)))) {
                return $filtered;
            }
        } catch (\Throwable) {}

        try {
            if (!\count($label = $crawler->filterXPath(\sprintf('//label[.="%s"]', $value)))) {
                return new Crawler();
            }
        } catch (\Throwable) {}

        if (isset($label) && $id = $label->attr('for')) {
            return self::filterByType($crawler, self::ID, $id);
        }

        return new Crawler();
    }

    private static function create(string $type, string $value): self
    {
        return new self(\sprintf(self::SEPARATOR_FORMAT, $type, $value));
    }

    /**
     * @return array{0:string|null,1:string}
     */
    private function parse(): array
    {
        if (1 === \count($parts = \explode(self::SEPARATOR, $this->value, 2))) {
            return [null, $parts[0]];
        }

        return [$parts[0], $parts[1]];
    }
}
