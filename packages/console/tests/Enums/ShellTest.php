<?php

declare(strict_types=1);

namespace Tempest\Console\Tests\Enums;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Console\Enums\Shell;

/**
 * @internal
 */
final class ShellTest extends TestCase
{
    #[Test]
    #[DataProvider('detectDataProvider')]
    public function detect(string|false $shellEnv, ?Shell $expected): void
    {
        $originalShell = getenv('SHELL');

        if ($shellEnv === false) {
            putenv('SHELL');
        } else {
            putenv("SHELL={$shellEnv}");
        }

        try {
            $result = Shell::detect();
            $this->assertSame($expected, $result);
        } finally {
            if ($originalShell === false) {
                putenv('SHELL');
            } else {
                putenv("SHELL={$originalShell}");
            }
        }
    }

    public static function detectDataProvider(): array
    {
        return [
            'zsh' => ['/bin/zsh', Shell::ZSH],
            'bash' => ['/bin/bash', Shell::BASH],
            'usr local zsh' => ['/usr/local/bin/zsh', Shell::ZSH],
            'usr local bash' => ['/usr/local/bin/bash', Shell::BASH],
            'fish' => ['/bin/fish', null],
            'empty' => ['', null],
            'not set' => [false, null],
        ];
    }

    #[Test]
    public function getCompletionsDirectory(): void
    {
        $home = $_SERVER['HOME'] ?? getenv('HOME') ?: '';

        $this->assertSame($home . '/.zsh/completions', Shell::ZSH->getCompletionsDirectory());
        $this->assertSame($home . '/.bash_completion.d', Shell::BASH->getCompletionsDirectory());
    }

    #[Test]
    public function getCompletionFilename(): void
    {
        $this->assertSame('_tempest', Shell::ZSH->getCompletionFilename());
        $this->assertSame('tempest.bash', Shell::BASH->getCompletionFilename());
    }

    #[Test]
    public function getInstalledCompletionPath(): void
    {
        $home = $_SERVER['HOME'] ?? getenv('HOME') ?: '';

        $this->assertSame($home . '/.zsh/completions/_tempest', Shell::ZSH->getInstalledCompletionPath());
        $this->assertSame($home . '/.bash_completion.d/tempest.bash', Shell::BASH->getInstalledCompletionPath());
    }

    #[Test]
    public function getSourceFilename(): void
    {
        $this->assertSame('completion.zsh', Shell::ZSH->getSourceFilename());
        $this->assertSame('completion.bash', Shell::BASH->getSourceFilename());
    }

    #[Test]
    public function getRcFile(): void
    {
        $home = $_SERVER['HOME'] ?? getenv('HOME') ?: '';

        $this->assertSame($home . '/.zshrc', Shell::ZSH->getRcFile());
        $this->assertSame($home . '/.bashrc', Shell::BASH->getRcFile());
    }

    #[Test]
    public function getPostInstallInstructions(): void
    {
        $zshInstructions = Shell::ZSH->getPostInstallInstructions();
        $this->assertIsArray($zshInstructions);
        $this->assertNotEmpty($zshInstructions);
        $this->assertStringContainsString('fpath', $zshInstructions[0]);

        $bashInstructions = Shell::BASH->getPostInstallInstructions();
        $this->assertIsArray($bashInstructions);
        $this->assertNotEmpty($bashInstructions);
        $this->assertStringContainsStringIgnoringCase('source', $bashInstructions[0]);
    }
}
