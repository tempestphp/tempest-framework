<?php

declare(strict_types=1);

namespace Tempest\Idempotency\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
final readonly class IdempotentRoute
{
    public function __construct(
        public ?bool $requireKey = null,
        public ?string $header = null,
    ) {}
}
