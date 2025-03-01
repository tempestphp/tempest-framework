<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Mapper\Fixtures;

use Tempest\Mapper\Attributes\MapTo;

final class ObjectWithMapToCollisions
{
    public function __construct(
        #[MapTo('name')]
        public readonly string $first_name,
        #[MapTo('full_name')]
        public readonly string $name,
        public readonly string $last_name,
    ) {
    }
}
