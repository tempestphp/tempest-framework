<?php

declare(strict_types=1);

namespace Tempest\Console\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Console\CompletionRuntime;
use Tempest\Console\Enums\Shell;

/**
 * @internal
 */
final class CompletionRuntimeTest extends TestCase
{
    private CompletionRuntime $completionRuntime;

    protected function setUp(): void
    {
        parent::setUp();

        if (PHP_OS_FAMILY === 'Windows') {
            $this->markTestSkipped('Shell completion is not supported on Windows.');
        }

        $this->completionRuntime = new CompletionRuntime();
    }

    #[Test]
    #[DataProvider('supportedPlatformDataProvider')]
    public function isSupportedPlatform(string $osFamily, bool $expected): void
    {
        $this->assertSame($expected, $this->completionRuntime->isSupportedPlatform($osFamily));
    }

    public static function supportedPlatformDataProvider(): array
    {
        return [
            'linux' => ['Linux', true],
            'darwin' => ['Darwin', true],
            'windows' => ['Windows', false],
        ];
    }

    #[Test]
    public function getUnsupportedPlatformMessage(): void
    {
        $this->assertStringContainsString('Windows', $this->completionRuntime->getUnsupportedPlatformMessage());
    }

    #[Test]
    public function getInstallationDirectory(): void
    {
        $home = $_SERVER['HOME'] ?? getenv('HOME') ?: '';

        $this->assertSame($home . '/.tempest/completion', $this->completionRuntime->getInstallationDirectory());
    }

    #[Test]
    public function getInstalledCompletionPath(): void
    {
        $home = $_SERVER['HOME'] ?? getenv('HOME') ?: '';

        $this->assertSame($home . '/.tempest/completion/tempest.zsh', $this->completionRuntime->getInstalledCompletionPath(Shell::ZSH));
        $this->assertSame($home . '/.tempest/completion/tempest.bash', $this->completionRuntime->getInstalledCompletionPath(Shell::BASH));
    }

    #[Test]
    public function getPostInstallInstructions(): void
    {
        $zshInstructions = $this->completionRuntime->getPostInstallInstructions(Shell::ZSH);
        $this->assertNotEmpty($zshInstructions);
        $this->assertStringContainsStringIgnoringCase('source', implode("\n", $zshInstructions));

        $bashInstructions = $this->completionRuntime->getPostInstallInstructions(Shell::BASH);
        $this->assertNotEmpty($bashInstructions);
        $this->assertStringContainsStringIgnoringCase('source', implode("\n", $bashInstructions));
    }
}
