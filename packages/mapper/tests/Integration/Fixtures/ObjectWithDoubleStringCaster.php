<?php

declare(strict_types=1);

namespace Tempest\Mapper\Tests\Integration\Fixtures;

use Tempest\Mapper\CastWith;

final class ObjectWithDoubleStringCaster
{
    #[CastWith(DoubleStringCaster::class)]
    public string $prop;
}
