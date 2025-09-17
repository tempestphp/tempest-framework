<?php

declare(strict_types=1);

namespace Tempest\Generation\Tests;

use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use Spatie\Snapshots\Driver;
use Spatie\Snapshots\MatchesSnapshots;

/**
 * @internal
 */
abstract class TestCase extends PHPUnitTestCase
{
    use MatchesSnapshots {
        assertMatchesSnapshot as baseAssertMatchesSnapshot;
    }

    public function assertMatchesSnapshot(mixed $actual, ?Driver $driver = null): void
    {
        if ($this->isWindows()) {
            $this->markTestSkipped('Snapshots are not supported on Windows.');
        }

        $this->baseAssertMatchesSnapshot($actual, $driver);
    }

    private function isWindows(): bool
    {
        return PHP_OS_FAMILY === 'Windows';
    }
}
