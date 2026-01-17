<?php

namespace Tempest\Generation\Tests;

use Spatie\Snapshots\Driver;
use Spatie\Snapshots\MatchesSnapshots as BaseMatchesSnapshots;

trait MatchesSnapshots
{
    use BaseMatchesSnapshots {
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
