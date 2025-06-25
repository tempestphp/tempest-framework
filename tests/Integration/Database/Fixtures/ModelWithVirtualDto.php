<?php

namespace Tests\Tempest\Integration\Database\Fixtures;

use Tempest\Database\IsDatabaseModel;
use Tempest\Database\Virtual;

final class ModelWithVirtualDto
{
    use IsDatabaseModel;

    #[Virtual]
    public DtoForModelWithVirtual $dto;
}
