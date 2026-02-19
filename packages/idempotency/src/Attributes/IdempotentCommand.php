<?php

declare(strict_types=1);

namespace Tempest\Idempotency\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class IdempotentCommand
{
    public function __construct(
        public ?int $ttlInSeconds = null,
        public ?int $pendingTtlInSeconds = null,
    ) {}
}
