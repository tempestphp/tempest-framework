<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Mapper\Fixtures;

use Tempest\Mapper\CastWith;

#[CastWith(MyObjectCaster::class)]
final class MyObject
{
    public function __construct(
        public string $name,
    ) {}
}
