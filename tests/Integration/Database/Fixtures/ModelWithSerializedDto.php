<?php

namespace Tests\Tempest\Integration\Database\Fixtures;

use Tempest\Database\IsDatabaseModel;

final class ModelWithSerializedDto
{
    use IsDatabaseModel;

    public DtoForModelWithSerializer $dto;
}
