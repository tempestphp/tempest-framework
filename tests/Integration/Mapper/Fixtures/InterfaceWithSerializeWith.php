<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Mapper\Fixtures;

use Tempest\Mapper\CastWith;
use Tempest\Mapper\SerializeWith;

#[CastWith(InterfaceValueCaster::class)]
#[SerializeWith(InterfaceValueSerializer::class)]
interface InterfaceWithSerializeWith
{
    public function getValue(): string;
}
