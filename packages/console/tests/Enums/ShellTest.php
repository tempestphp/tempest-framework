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
    protected function setUp(): void
    {
        parent::setUp();

        if (PHP_OS_FAMILY === 'Windows') {
            $this->markTestSkipped('Shell completion is not supported on Windows.');
        }
    }

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
    public function getCompletionFilename(): void
    {
        $this->assertSame('tempest.zsh', Shell::ZSH->getCompletionFilename());
        $this->assertSame('tempest.bash', Shell::BASH->getCompletionFilename());
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
}
