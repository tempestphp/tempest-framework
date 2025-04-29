<?php

declare(strict_types=1);

namespace Tempest\Mapper\Tests\Integration\Fixtures;

use Tempest\Database\IsDatabaseModel;
use Tempest\Mapper\CastWith;

final class ObjectFactoryA
{
    use IsDatabaseModel;

    #[CastWith(ObjectFactoryACaster::class)]
    public string $prop;
}
