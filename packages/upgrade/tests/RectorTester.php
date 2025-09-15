<?php

namespace Tempest\Upgrade\Tests;

use Closure;
use PHPUnit\Framework\Assert;
use Rector\Application\ApplicationFileProcessor;
use Rector\DependencyInjection\RectorContainerFactory;
use Rector\ValueObject\Bootstrap\BootstrapConfigs;
use Rector\ValueObject\Configuration;

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
        [$expected, $actual] = preg_replace('/^<\?php\s*/', '', [$expected, $this->actual]);
        $expected = trim($expected);
        $actual = trim($actual);

        Assert::assertSame($expected, $actual);

        return $this;
    }

    public function assertContains(string $needle): self
    {
        Assert::assertStringContainsString($needle, $this->actual);

        return $this;
    }

    public function assertNotContains(string $needle): self
    {
        Assert::assertStringNotContainsString($needle, $this->actual);

        return $this;
    }

    private function getActual(string $fixturePath): string
    {
        $rectorContainerFactory = new RectorContainerFactory();
        $bootstrapConfigs = new BootstrapConfigs($this->configPath, []);
        $container = $rectorContainerFactory->createFromBootstrapConfigs($bootstrapConfigs);

        $config = new Configuration(
            isDryRun: true,
            shouldClearCache: true,
            showDiffs: true,
        );

        $processer = $container->make(ApplicationFileProcessor::class);

        $diff = $processer->processFiles([$fixturePath], $config)->getFileDiffs()[0] ?? null;

        return $this->cleanDiff($diff?->getDiff() ?? '');
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
