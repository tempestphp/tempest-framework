<?php

declare(strict_types=1);

namespace Tempest\Support\Tests\Integration\Fixtures;

final readonly class TestObject
{
    public function __construct(
        public string $name,
    ) {}
}
