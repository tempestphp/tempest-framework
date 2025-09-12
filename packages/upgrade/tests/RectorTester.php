<?php

namespace Tempest\Upgrade\Tests;

use Closure;
use PHPUnit\Framework\Assert;

final class RectorTester
{
    private(set) string $fixturePath;
    private(set) string $actual;

    public function __construct(
        private readonly string $configPath,
    ) {}

    public function runFixture(string $fixturePath): self
    {
        $clone = clone $this;

        $clone->fixturePath = $fixturePath;
        $clone->actual = $this->getActual($fixturePath);

        return $clone;
    }

    /**
     * @param Closure(string $actual): void $test
     */
    public function assert(Closure $test): self
    {
        $test($this->actual);

        return $this;
    }

    public function assertMatchesExpected(): self
    {
        $expected = file_get_contents(str_replace('.input.php', '.expected.php', $this->fixturePath));
        $expected = preg_replace('/^<\?php\n/', '', $expected);
        $expected = trim($expected);

        Assert::assertSame($expected, $this->actual);

        return $this;
    }

    public function assertContains(string $needle): self
    {
        Assert::assertStringContainsString($needle, $this->actual);

        return $this;
    }

    private function getActual(string $fixturePath): string
    {
        $command = "vendor/bin/rector process {$fixturePath} --config {$this->configPath} --dry-run --output-format=json";

        $output = json_decode(shell_exec($command), associative: true);

        return $this->cleanDiff($output['file_diffs'][0]['diff'] ?? '');
    }

    private function cleanDiff(string $diff): string
    {
        $diff = preg_replace('/^\+\+\+.*\n/m', '', $diff);
        $diff = preg_replace('/^@@.*?@@\n/m', '', $diff);
        $diff = preg_replace('/\\\\ No newline at end of file\n/', '', $diff);

        $diff = preg_replace('/^\s/m', '', $diff);

        $diff = preg_replace('/^-.*\n/m', '', $diff);
        $diff = preg_replace('/^\+/m', '', $diff);

        return trim($diff);
    }

}