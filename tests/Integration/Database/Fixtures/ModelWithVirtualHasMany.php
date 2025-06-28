<?php

namespace Tests\Tempest\Integration\Database\Fixtures;

use Tempest\Database\IsDatabaseModel;
use Tempest\Database\Virtual;

final class ModelWithVirtualHasMany
{
    use IsDatabaseModel;

    #[Virtual]
    /** @var \Tests\Tempest\Integration\Database\Fixtures\DtoForModelWithVirtual[] $dto */
    public array $dtos;
}
