<?php

declare(strict_types=1);

namespace Tempest\Idempotency\Store;

use Tempest\Cache\Cache;
use Tempest\DateTime\Duration;
use Tempest\Idempotency\Support\IdempotencyKeyResolver;

final readonly class CacheIdempotencyStore implements IdempotencyStore
{
    public function __construct(
        private Cache $cache,
        private IdempotencyKeyResolver $keyResolver,
    ) {}

    public function find(string $scope, string $key): ?IdempotencyRecord
    {
        $record = $this->cache->get($this->keyResolver->recordKey($scope, $key));

        if (! $record instanceof IdempotencyRecord) {
            return null;
        }

        return $record;
    }

    public function savePending(string $scope, string $key, string $fingerprint, int $ttlInSeconds, ?string $pendingOwner = null, ?int $pendingHeartbeatAt = null): void
    {
        $this->cache->put(
            key: $this->keyResolver->recordKey($scope, $key),
            value: new IdempotencyRecord(
                fingerprint: $fingerprint,
                state: IdempotencyState::PENDING,
                pendingOwner: $pendingOwner,
                pendingHeartbeatAt: $pendingHeartbeatAt,
            ),
            expiration: Duration::seconds($ttlInSeconds),
        );
    }

    public function updateHeartbeat(string $scope, string $key, string $owner, int $heartbeatAt, int $ttlInSeconds): void
    {
        $record = $this->find($scope, $key);

        if (! $record instanceof IdempotencyRecord || $record->state !== IdempotencyState::PENDING || $record->pendingOwner !== $owner) {
            return;
        }

        $this->cache->put(
            key: $this->keyResolver->recordKey($scope, $key),
            value: new IdempotencyRecord(
                fingerprint: $record->fingerprint,
                state: $record->state,
                response: $record->response,
                pendingOwner: $record->pendingOwner,
                pendingHeartbeatAt: $heartbeatAt,
            ),
            expiration: Duration::seconds($ttlInSeconds),
        );
    }

    public function saveCompleted(string $scope, string $key, string $fingerprint, ?StoredResponse $response, int $ttlInSeconds): void
    {
        $this->cache->put(
            key: $this->keyResolver->recordKey($scope, $key),
            value: new IdempotencyRecord(
                fingerprint: $fingerprint,
                state: IdempotencyState::COMPLETED,
                response: $response,
            ),
            expiration: Duration::seconds($ttlInSeconds),
        );
    }

    public function delete(string $scope, string $key): void
    {
        $this->cache->remove($this->keyResolver->recordKey($scope, $key));
    }
}
