<?php

declare(strict_types=1);

namespace Tempest\Support\Tests\JavaScript;

use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Tempest\Support\JavaScript\PackageManager;

/**
 * @internal
 */
final class PackageManagerTest extends TestCase
{
    #[TestWith(['bun-lock', PackageManager::BUN])]
    #[TestWith(['bun-lockb', PackageManager::BUN])]
    #[TestWith(['npm', PackageManager::NPM])]
    #[TestWith(['yarn', PackageManager::YARN])]
    #[TestWith(['pnpm', PackageManager::PNPM])]
    #[TestWith(['multiple', PackageManager::BUN])]
    #[TestWith(['empty', null])]
    public function test_can_detect_package_manager(string $fixture, ?PackageManager $expectedPackageManager): void
    {
        $this->assertSame(
            expected: $expectedPackageManager,
            actual: PackageManager::detect(cwd: __DIR__ . "/Fixtures/{$fixture}"),
        );
    }

    #[TestWith(['bun-lock', 'bun dev'])]
    #[TestWith(['bun-lockb', 'bun dev'])]
    #[TestWith(['npm', 'npm run dev'])]
    #[TestWith(['yarn', 'yarn dev'])]
    #[TestWith(['pnpm', 'pnpm dev'])]
    #[TestWith(['multiple', 'bun dev'])]
    #[TestWith(['empty', null])]
    public function test_print_run_command(string $fixture, ?string $expectedCommand): void
    {
        $this->assertSame(
            expected: $expectedCommand,
            actual: PackageManager::detect(cwd: __DIR__ . "/Fixtures/{$fixture}")?->getRunCommand('dev'),
        );
    }
}
