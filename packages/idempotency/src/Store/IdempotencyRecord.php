<?php

declare(strict_types=1);

namespace Tempest\Idempotency\Store;

final readonly class IdempotencyRecord
{
    public function __construct(
        public string $fingerprint,
        public IdempotencyState $state,
        public ?StoredResponse $response = null,
        public ?string $pendingOwner = null,
        public ?int $pendingHeartbeatAt = null,
    ) {}
}
