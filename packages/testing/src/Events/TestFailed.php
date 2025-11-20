<?php

namespace Tempest\Testing\Events;

use Tempest\Testing\Exceptions\TestHasFailed;

final readonly class TestFailed
{
    public function __construct(
        public string $name,
        public TestHasFailed $exception,
    ) {}
}