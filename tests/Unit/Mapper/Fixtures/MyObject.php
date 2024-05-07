<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Mapper\Fixtures;

use Tempest\Mapper\CastWith;

#[CastWith(MyObjectCaster::class)]
final readonly class MyObject
{
    public function __construct(public string $name)
    {
    }
}
