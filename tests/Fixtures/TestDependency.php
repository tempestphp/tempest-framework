<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures;

final readonly class TestDependency
{
    public function __construct(
        public string $input,
    ) {}
}
