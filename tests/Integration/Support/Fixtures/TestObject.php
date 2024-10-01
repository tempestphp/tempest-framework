<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Support\Fixtures;

final readonly class TestObject
{
    public function __construct(
        public string $name,
    ) {
    }
}
