<?php

declare(strict_types=1);

namespace Tempest\Idempotency\Tests;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Tempest\Cache\GenericCache;
use Tempest\Idempotency\Config\IdempotencyConfig;
use Tempest\Idempotency\Store\CacheIdempotencyStore;
use Tempest\Idempotency\Store\IdempotencyState;
use Tempest\Idempotency\Support\HeartbeatRenewer;
use Tempest\Idempotency\Support\IdempotencyKeyResolver;

final class HeartbeatRenewerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (PHP_OS_FAMILY === 'Windows') {
            $this->markTestSkipped('Idempotency tests are not supported on Windows.');
        }
    }

    #[Test]
    public function update_heartbeat_refreshes_pending_record_timestamp(): void
    {
        $cache = new GenericCache(new ArrayAdapter());
        $config = new IdempotencyConfig();
        $resolver = new IdempotencyKeyResolver($config);
        $store = new CacheIdempotencyStore($cache, $resolver);

        $store->savePending(
            scope: 'test-scope',
            key: 'test-key',
            fingerprint: 'abc123',
            ttlInSeconds: 120,
            pendingOwner: 'host|123|token',
            pendingHeartbeatAt: 1000,
        );

        $store->updateHeartbeat('test-scope', 'test-key', 'host|123|token', 2000, 120);

        $record = $store->find('test-scope', 'test-key');

        $this->assertNotNull($record);
        $this->assertSame(IdempotencyState::PENDING, $record->state);
        $this->assertSame(2000, $record->pendingHeartbeatAt);
        $this->assertSame('abc123', $record->fingerprint);
        $this->assertSame('host|123|token', $record->pendingOwner);
    }

    #[Test]
    public function update_heartbeat_does_nothing_when_record_does_not_exist(): void
    {
        $cache = new GenericCache(new ArrayAdapter());
        $config = new IdempotencyConfig();
        $resolver = new IdempotencyKeyResolver($config);
        $store = new CacheIdempotencyStore($cache, $resolver);

        $store->updateHeartbeat('test-scope', 'missing-key', 'host|123|token', 2000, 120);

        $this->assertNull($store->find('test-scope', 'missing-key'));
    }

    #[Test]
    public function update_heartbeat_does_nothing_when_record_is_completed(): void
    {
        $cache = new GenericCache(new ArrayAdapter());
        $config = new IdempotencyConfig();
        $resolver = new IdempotencyKeyResolver($config);
        $store = new CacheIdempotencyStore($cache, $resolver);

        $store->saveCompleted(
            scope: 'test-scope',
            key: 'test-key',
            fingerprint: 'abc123',
            response: null,
            ttlInSeconds: 120,
        );

        $store->updateHeartbeat('test-scope', 'test-key', 'host|123|token', 2000, 120);

        $record = $store->find('test-scope', 'test-key');

        $this->assertNotNull($record);
        $this->assertSame(IdempotencyState::COMPLETED, $record->state);
        $this->assertNull($record->pendingHeartbeatAt);
    }

    #[Test]
    public function update_heartbeat_does_nothing_when_owner_does_not_match(): void
    {
        $cache = new GenericCache(new ArrayAdapter());
        $config = new IdempotencyConfig();
        $resolver = new IdempotencyKeyResolver($config);
        $store = new CacheIdempotencyStore($cache, $resolver);

        $store->savePending(
            scope: 'test-scope',
            key: 'test-key',
            fingerprint: 'abc123',
            ttlInSeconds: 120,
            pendingOwner: 'host|123|token',
            pendingHeartbeatAt: 1000,
        );

        $store->updateHeartbeat('test-scope', 'test-key', 'other-host|456|other', 2000, 120);

        $record = $store->find('test-scope', 'test-key');

        $this->assertNotNull($record);
        $this->assertSame(1000, $record->pendingHeartbeatAt);
    }

    #[Test]
    public function renewer_updates_heartbeat_during_processing(): void
    {
        $cache = new GenericCache(new ArrayAdapter());
        $config = new IdempotencyConfig();
        $resolver = new IdempotencyKeyResolver($config);
        $store = new CacheIdempotencyStore($cache, $resolver);

        $initialTime = time() - 10;

        $store->savePending(
            scope: 'test-scope',
            key: 'test-key',
            fingerprint: 'abc123',
            ttlInSeconds: 120,
            pendingOwner: 'host|123|token',
            pendingHeartbeatAt: $initialTime,
        );

        $renewer = new HeartbeatRenewer();
        $renewer->start(
            store: $store,
            scope: 'test-scope',
            key: 'test-key',
            owner: 'host|123|token',
            intervalInSeconds: 1,
            recordTtlInSeconds: 120,
        );

        sleep(2);

        $renewer->stop();

        $record = $store->find('test-scope', 'test-key');

        $this->assertNotNull($record);
        $this->assertGreaterThan($initialTime, $record->pendingHeartbeatAt);
    }

    #[Test]
    public function renewer_stop_is_safe_when_not_started(): void
    {
        $this->expectNotToPerformAssertions();

        $renewer = new HeartbeatRenewer();
        $renewer->stop();
    }
}
