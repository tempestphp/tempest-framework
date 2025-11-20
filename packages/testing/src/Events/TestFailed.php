<?php

namespace Tempest\Testing\Events;

use Tempest\Testing\Exceptions\TestHasFailed;

final readonly class TestFailed
{
    public function __construct(
        public string $name,
        public string $reason,
        public string $location,
    ) {}

    public static function fromException(string $name, TestHasFailed $exception): self
    {
        return new self(
            name: $name,
            reason: $exception->reason,
            location: $exception->location,
        );
    }
}