<?php

namespace Tests\Tempest\Integration\Database\Fixtures;

use Tempest\Mapper\Casters\DtoCaster;
use Tempest\Mapper\CastWith;
use Tempest\Mapper\Serializers\DtoSerializer;
use Tempest\Mapper\SerializeWith;

#[CastWith(DtoCaster::class)]
#[SerializeWith(DtoSerializer::class)]
final class DtoForModelWithSerializer
{
    public function __construct(
        public string $data,
    ) {}
}
