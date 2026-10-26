<?php

declare(strict_types=1);

namespace Tempest\Idempotency;

final class IdempotencyConfig
{
    public function __construct(
        public string $header = 'Idempotency-Key',
        public bool $requireKey = true,
        public int $ttlInSeconds = 86_400,
        public int $pendingTtlInSeconds = 60,
        public string $cachePrefix = 'idempotency',
        /** @var class-string<IdempotencyStore> */
        public string $storeClass = CacheIdempotencyStore::class,
    ) {}
}
