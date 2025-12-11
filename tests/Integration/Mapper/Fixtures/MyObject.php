<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Mapper\Fixtures;

use Tempest\Mapper\CastWith;
use Tempest\Mapper\SerializeAs;

#[SerializeAs(self::class)]
#[CastWith(MyObjectCaster::class)]
final class MyObject
{
    public function __construct(
        public string $name,
    ) {}
}
