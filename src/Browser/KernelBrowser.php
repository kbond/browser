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

use Symfony\Bundle\FrameworkBundle\KernelBrowser as SymfonyKernelBrowser;
use Zenstruck\Browser;
use Zenstruck\Browser\Session\KernelSession;
use Zenstruck\Callback\Parameter;
use Zenstruck\Dom\Selector;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @phpstan-import-type SelectorType from Selector
 * @phpstan-import-type Options from HttpOptions
 *
 * @method SymfonyKernelBrowser client()
 */
class KernelBrowser extends Browser
{
    protected ?HttpOptions $defaultHttpOptions = null;

    private KernelSession $session;

    /**
     * @internal
     */
    final public function __construct(SymfonyKernelBrowser $client, array $options = [])
    {
        parent::__construct($this->session = new KernelSession($client), $options);
    }

    /**
     * @see SymfonyKernelBrowser::disableReboot()
     *
     * @return static
     */
    final public function disableReboot(): self
    {
        $this->client()->disableReboot();

        return $this;
    }

    /**
     * @see SymfonyKernelBrowser::enableReboot()
     *
     * @return static
     */
    final public function enableReboot(): self
    {
        $this->client()->enableReboot();

        return $this;
    }

    /**
     * @param HttpOptions|Options $options
     *
     * @return static
     */
    final public function setDefaultHttpOptions(array|HttpOptions $options): self
    {
        $this->defaultHttpOptions = HttpOptions::create($options);

        return $this;
    }

    /**
     * @param HttpOptions|Options $options
     *
     * @return static
     */
    final public function request(string $method, string $url, array|HttpOptions $options = []): self
    {
        if ($this->defaultHttpOptions) {
            // clone to avoid HttpOptions::merge() mutating the default options
            $options = (clone $this->defaultHttpOptions)->merge($options);
        }

        $options = HttpOptions::create($options);

        $this->wrapRequest(fn() => $this->client()->request(
            $method,
            $options->addQueryToUrl($url),
            $options->parameters(),
            $options->files(),
            $options->server(),
            $options->body(),
        ));

        return $this;
    }

    /**
     * @see request()
     *
     * @param HttpOptions|Options $options
     *
     * @return static
     */
    final public function get(string $url, array|HttpOptions $options = []): self
    {
        return $this->request('GET', $url, $options);
    }

    /**
     * @see request()
     *
     * @param HttpOptions|Options $options
     *
     * @return static
     */
    final public function post(string $url, array|HttpOptions $options = []): self
    {
        return $this->request('POST', $url, $options);
    }

    /**
     * @see request()
     *
     * @param HttpOptions|Options $options
     *
     * @return static
     */
    final public function put(string $url, array|HttpOptions $options = []): self
    {
        return $this->request('PUT', $url, $options);
    }

    /**
     * @see request()
     *
     * @param HttpOptions|Options $options
     *
     * @return static
     */
    final public function delete(string $url, array|HttpOptions $options = []): self
    {
        return $this->request('DELETE', $url, $options);
    }

    /**
     * @see request()
     *
     * @param HttpOptions|Options $options
     *
     * @return static
     */
    final public function patch(string $url, array|HttpOptions $options = []): self
    {
        return $this->request('PATCH', $url, $options);
    }

    /**
     * @return static
     */
    final public function assertJson(): self
    {
        return $this->assertContentType('json');
    }

    /**
     * @return static
     */
    final public function assertXml(): self
    {
        return $this->assertContentType('xml');
    }

    /**
     * @return static
     */
    final public function assertHtml(): self
    {
        return $this->assertContentType('html');
    }

    /**
     * @param string $expression JMESPath expression
     * @param mixed  $expected
     *
     * @return static
     */
    final public function assertJsonMatches(string $expression, $expected): self
    {
        $this->json()->assertMatches($expression, $expected);

        return $this;
    }

    final public function json(): Json
    {
        return new Json($this->assertJson()->content());
    }

    final public function dump(Selector|string|callable|null $selector = null): self
    {
        if (!$selector) {
            Dumper::dump($this->source(true));

            return $this;
        }

        $contentType = $this->normalizedContentType();

        match (true) {
            'json' === $contentType && \is_string($selector) => $this->json()->dump($selector),
            'dom' === $contentType => $this->dom()->dump($selector),
            default => $this->dump(),
        };

        return $this;
    }

    /**
     * @internal
     */
    final protected function source(bool $debug): string
    {
        $ret = '';
        $contentType = $this->normalizedContentType();

        // We never want to prepend non-text files with metadata.
        if ($debug && $contentType) {
            $ret .= "<!--\n";
            $ret .= "URL: {$this->session->currentUrl()} ({$this->session->statusCode()})\n\n";

            foreach ($this->session->responseHeaders() as $header => $values) {
                foreach ((array) $values as $value) {
                    $ret .= "{$header}: {$value}\n";
                }
            }

            $ret .= "-->\n";
        }

        return $ret.('json' === $contentType ? $this->json() : $this->content());
    }

    protected function useParameters(): array
    {
        return [
            ...parent::useParameters(),
            Parameter::typed(Json::class, Parameter::factory(fn() => $this->json())),
        ];
    }

    /**
     * @return "dom"|"json"|"text"|null
     */
    private function normalizedContentType(): ?string
    {
        $contentType = (string) $this->session->responseHeader('Content-Type');

        return match (true) {
            \str_contains($contentType, 'json') => 'json',
            \str_contains($contentType, 'html') => 'dom',
            \str_contains($contentType, 'xml') => 'dom',
            \str_contains($contentType, 'text') => 'text',
            default => null,
        };
    }
}
