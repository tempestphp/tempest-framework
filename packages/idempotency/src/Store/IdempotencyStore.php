<?php

declare(strict_types=1);

namespace Tempest\Idempotency\Store;

interface IdempotencyStore
{
    public function find(string $scope, string $key): ?IdempotencyRecord;

    public function savePending(string $scope, string $key, string $fingerprint, int $ttlInSeconds, ?string $pendingOwner = null, ?int $pendingHeartbeatAt = null): void;

    public function updateHeartbeat(string $scope, string $key, string $owner, int $heartbeatAt, int $ttlInSeconds): void;

    public function saveCompleted(string $scope, string $key, string $fingerprint, ?StoredResponse $response, int $ttlInSeconds): void;

    public function delete(string $scope, string $key): void;
}
