<?php

declare(strict_types=1);

namespace Tempest\Idempotency\Tests\Fixtures;

use Tempest\Cache\Cache;
use Tempest\Idempotency\Store\CacheIdempotencyStore;
use Tempest\Idempotency\Store\IdempotencyRecord;
use Tempest\Idempotency\Store\IdempotencyStore;
use Tempest\Idempotency\Store\StoredResponse;
use Tempest\Idempotency\Support\IdempotencyKeyResolver;

final class RecordingStore implements IdempotencyStore
{
    public ?array $lastSavePending = null;

    private readonly CacheIdempotencyStore $store;

    public function __construct(Cache $cache, IdempotencyKeyResolver $resolver)
    {
        $this->store = new CacheIdempotencyStore($cache, $resolver);
    }

    public function find(string $scope, string $key): ?IdempotencyRecord
    {
        return $this->store->find($scope, $key);
    }

    public function savePending(string $scope, string $key, string $fingerprint, int $ttlInSeconds, ?string $pendingOwner = null, ?int $pendingHeartbeatAt = null): void
    {
        $this->lastSavePending = [
            'scope' => $scope,
            'key' => $key,
            'fingerprint' => $fingerprint,
            'ttlInSeconds' => $ttlInSeconds,
            'pendingOwner' => $pendingOwner,
            'pendingHeartbeatAt' => $pendingHeartbeatAt,
        ];

        $this->store->savePending($scope, $key, $fingerprint, $ttlInSeconds, $pendingOwner, $pendingHeartbeatAt);
    }

    public function updateHeartbeat(string $scope, string $key, string $owner, int $heartbeatAt, int $ttlInSeconds): void
    {
        $this->store->updateHeartbeat($scope, $key, $owner, $heartbeatAt, $ttlInSeconds);
    }

    public function saveCompleted(string $scope, string $key, string $fingerprint, ?StoredResponse $response, int $ttlInSeconds): void
    {
        $this->store->saveCompleted($scope, $key, $fingerprint, $response, $ttlInSeconds);
    }

    public function delete(string $scope, string $key): void
    {
        $this->store->delete($scope, $key);
    }
}
