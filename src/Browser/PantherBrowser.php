<?php

namespace Zenstruck\Browser;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Panther\Client;
use Symfony\Component\VarDumper\VarDumper;
use Zenstruck\Browser;
use Zenstruck\Browser\Extension\Html;
use Zenstruck\Browser\Mink\PantherDriver;
use Zenstruck\Browser\Response\PantherResponse;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @experimental in 1.0
 */
class PantherBrowser extends Browser
{
    use Html;

    private Client $client;
    private ?string $screenshotDir = null;
    private ?string $consoleLogDir = null;

    final public function __construct(Client $client)
    {
        parent::__construct(new PantherDriver($this->client = $client));
    }

    final public function client(): Client
    {
        return $this->client;
    }

    final public function setScreenshotDir(string $dir): self
    {
        $this->screenshotDir = $dir;

        return $this;
    }

    final public function setConsoleLogDir(string $dir): self
    {
        $this->consoleLogDir = $dir;

        return $this;
    }

    /**
     * @return static
     */
    final public function assertVisible(string $selector): self
    {
        Assert::wrapMinkExpectation(function() use ($selector) {
            $element = $this->webAssert()->elementExists('css', $selector);

            Assert::true($element->isVisible(), 'Element "%s" is not visible.', $selector);
        });

        return $this;
    }

    /**
     * @return static
     */
    final public function assertNotVisible(string $selector): self
    {
        if (!$element = $this->documentElement()->find('css', $selector)) {
            // element does not exist and is therefore not visible
            Assert::pass();

            return $this;
        }

        Assert::false($element->isVisible(), 'Element "%s" is visible but it should not be.', $selector);

        return $this;
    }

    /**
     * @return static
     */
    final public function wait(int $milliseconds): self
    {
        \usleep($milliseconds * 1000);

        return $this;
    }

    /**
     * @return static
     */
    final public function waitUntilVisible(string $selector): self
    {
        $this->client->waitForVisibility($selector);

        return $this;
    }

    /**
     * @return static
     */
    final public function waitUntilNotVisible(string $selector): self
    {
        $this->client->waitForInvisibility($selector);

        return $this;
    }

    /**
     * @return static
     */
    final public function waitUntilSeeIn(string $selector, string $expected): self
    {
        $this->client->waitForElementToContain($selector, $expected);

        return $this;
    }

    /**
     * @return static
     */
    final public function waitUntilNotSeeIn(string $selector, string $expected): self
    {
        $this->client->waitForElementToNotContain($selector, $expected);

        return $this;
    }

    /**
     * @return static
     */
    final public function inspect(): self
    {
        if (!($_SERVER['PANTHER_NO_HEADLESS'] ?? false)) {
            throw new \RuntimeException('The "PANTHER_NO_HEADLESS" env variable must be set to inspect.');
        }

        \fwrite(STDIN, "\n\nInspecting the browser.\n\nPress enter to continue...");
        \fgets(STDIN);

        return $this;
    }

    /**
     * @return static
     */
    final public function takeScreenshot(string $filename): self
    {
        if ($this->screenshotDir) {
            $filename = \sprintf('%s/%s', \rtrim($this->screenshotDir, '/'), \ltrim($filename, '/'));
        }

        $this->client->takeScreenshot($filename);

        return $this;
    }

    final public function saveConsoleLog(string $filename): self
    {
        if ($this->consoleLogDir) {
            $filename = \sprintf('%s/%s', \rtrim($this->consoleLogDir, '/'), \ltrim($filename, '/'));
        }

        $log = $this->client->manage()->getLog('browser');
        $log = \json_encode($log, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);

        (new Filesystem())->dumpFile($filename, $log);

        return $this;
    }

    final public function dumpConsoleLog(): self
    {
        VarDumper::dump($this->client->manage()->getLog('browser'));

        return $this;
    }

    final public function ddConsoleLog(): void
    {
        $this->dumpConsoleLog();
        exit(1);
    }

    final public function dumpCurrentState(string $filename): void
    {
        parent::dumpCurrentState($filename);

        $this->takeScreenshot("{$filename}.png");
        $this->saveConsoleLog("{$filename}.log");
    }

    protected function response(): PantherResponse
    {
        return new PantherResponse($this->minkSession());
    }
}
