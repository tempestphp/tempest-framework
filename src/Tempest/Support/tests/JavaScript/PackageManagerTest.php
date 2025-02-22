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
}
