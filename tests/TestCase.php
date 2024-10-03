<?php

declare(strict_types=1);

namespace Tests\Tempest;

use PHPUnit\Framework\TestCase as FrameworkTestCase;
use Spatie\Snapshots\Driver;
use Spatie\Snapshots\MatchesSnapshots;

/**
 * @internal
 */
abstract class TestCase extends FrameworkTestCase
{
    use MatchesSnapshots {
        assertMatchesSnapshot as baseAssertMatchesSnapshot;
    }

    public function assertMatchesSnapshot($actual, ?Driver $driver = null): void
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
