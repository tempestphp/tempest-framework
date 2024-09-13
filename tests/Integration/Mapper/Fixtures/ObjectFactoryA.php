<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Mapper\Fixtures;

use Tempest\Database\DatabaseModel;
use Tempest\Database\IsDatabaseModel;
use Tempest\Mapper\CastWith;

final class ObjectFactoryA implements DatabaseModel
{
    use IsDatabaseModel;

    #[CastWith(ObjectFactoryACaster::class)]
    public string $prop;
}
