<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Mapper\Fixtures;

final readonly class Name
{
    public function __construct(
        public string $first,
        public string $last,
    ) {
    }
}
