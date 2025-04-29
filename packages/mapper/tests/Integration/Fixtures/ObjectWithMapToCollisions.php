<?php

declare(strict_types=1);

namespace Tempest\Mapper\Tests\Integration\Fixtures;

use Tempest\Mapper\MapTo;

final readonly class ObjectWithMapToCollisions
{
    public function __construct(
        #[MapTo('name')]
        public string $first_name,
        #[MapTo('full_name')]
        public string $name,
        public string $last_name,
    ) {}
}
