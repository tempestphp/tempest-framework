<?php

namespace Tests\Tempest\Integration\Database\Fixtures;

use Tempest\Database\IsDatabaseModel;
use Tempest\Mapper\Serializers\DtoSerializer;
use Tempest\Mapper\SerializeWith;

final class ModelWithSerializedDtoProperty
{
    use IsDatabaseModel;

    #[SerializeWith(DtoSerializer::class)]
    public DtoForModelWithSerializerOnProperty $dto;
}
