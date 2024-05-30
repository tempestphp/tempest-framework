<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Mapper\Fixtures;

use Tempest\Database\IsModel;
use Tempest\Database\Model;
use Tempest\Mapper\CastWith;

class ObjectFactoryA implements Model
{
    use IsModel;

    #[CastWith(ObjectFactoryACaster::class)]
    public string $prop;
}
