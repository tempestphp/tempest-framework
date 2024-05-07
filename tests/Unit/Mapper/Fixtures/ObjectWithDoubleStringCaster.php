<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Mapper\Fixtures;

use Tempest\Mapper\CastWith;

final readonly class ObjectWithDoubleStringCaster
{
    #[CastWith(DoubleStringCaster::class)]
    public string $prop;
}
