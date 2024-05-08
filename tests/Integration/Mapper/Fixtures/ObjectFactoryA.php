<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Mapper\Fixtures;

use Tempest\Mapper\CastWith;
use Tempest\ORM\IsModel;
use Tempest\ORM\Model;

class ObjectFactoryA implements Model
{
    use IsModel;

    #[CastWith(ObjectFactoryACaster::class)]
    public string $prop;
}
