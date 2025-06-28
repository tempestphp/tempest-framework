<?php

namespace Tests\Tempest\Integration\Cache;

use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Tempest\Cache\Config\InMemoryCacheConfig;
use Tempest\Cache\GenericCache;
use Tempest\Cache\LockAcquisitionTimedOut;
use Tempest\DateTime\Duration;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

final class LockTest extends FrameworkIntegrationTestCase
{
    public function test_lock(): void
    {
        $cache = new GenericCache(new ArrayAdapter());

        $lock = $cache->lock('processing');

        $this->assertTrue($lock->acquire());
        $this->assertTrue($lock->release());
    }

    public function test_same_lock_can_be_acquired_by_same_owner(): void
    {
        $cache = new GenericCache(new ArrayAdapter());

        $lock1 = $cache->lock('processing', owner: 'owner1');
        $lock2 = $cache->lock('processing', owner: 'owner1');

        $this->assertTrue($lock1->acquire());
        $this->assertSame('owner1', $lock1->owner);
        $this->assertSame('owner1', $lock2->owner);
        $this->assertSame('processing', $lock1->key);
        $this->assertSame('processing', $lock2->key);

        $this->assertFalse($lock2->acquire()); // can't re-acquire
        $this->assertTrue($lock2->release()); // but can release
    }

    public function test_same_lock_cannot_be_acquired_by_different_owners(): void
    {
        $cache = new GenericCache(new ArrayAdapter());

        $lock1 = $cache->lock('processing', owner: 'owner1');
        $lock2 = $cache->lock('processing', owner: 'owner2');

        $this->assertSame('owner1', $lock1->owner);
        $this->assertSame('owner2', $lock2->owner);
        $this->assertSame('processing', $lock1->key);
        $this->assertSame('processing', $lock2->key);

        $this->assertTrue($lock1->acquire());
        $this->assertFalse($lock2->acquire());

        $this->assertTrue($lock1->release());
        $this->assertTrue($lock2->acquire());
    }

    public function test_lock_with_ttl(): void
    {
        $clock = $this->clock();
        $cache = new GenericCache(new ArrayAdapter(clock: $clock->toPsrClock()));

        $lock = $cache->lock('processing', expiration: Duration::hours(1));

        $this->assertTrue($lock->acquire());
        $this->assertTrue($lock->expiration->equals($clock->now()->plusHours(1)));

        // Still locked after 30 min
        $clock->plus(Duration::minutes(30));
        $this->assertFalse($lock->acquire());

        // No longer locked after another 30 min (total 1h)
        $clock->plus(Duration::minutes(30));
        $this->assertTrue($lock->acquire());
        $this->assertFalse($lock->release());
    }

    public function test_lock_execution_without_timeout(): void
    {
        $cache = new GenericCache(new ArrayAdapter());

        $lock = $cache->lock('processing');

        $this->assertTrue($lock->execute(fn () => true)); // @phpstan-ignore method.alreadyNarrowedType
        $this->assertFalse($lock->release());
    }

    public function test_lock_execution_when_already_locked_by_another_owner(): void
    {
        $this->expectException(LockAcquisitionTimedOut::class);

        $cache = new GenericCache(new ArrayAdapter());

        // Lock externally
        $externalLock = $cache->lock('processing');
        $externalLock->acquire();

        // Try executing a callback, should timeout instantly
        $cache->lock('processing')->execute(fn () => true);
    }

    public function test_lock_execution_when_already_locked_by_another_owner_with_timeout(): void
    {
        $clock = $this->clock();
        $cache = new GenericCache(new ArrayAdapter(clock: $clock->toPsrClock()));

        // Lock externally for a set duration
        $externalLock = $cache->lock('processing', expiration: Duration::hours(1));
        $externalLock->acquire();

        // Skip the set duration
        $clock->plus(Duration::hours(1));

        // Try executing a callback for the specified duration
        $this->assertTrue($cache->lock('processing')->execute(fn () => true, wait: Duration::hours(1))); // @phpstan-ignore method.alreadyNarrowedType
    }
}
