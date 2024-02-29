<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Mapper\Fixtures;

use Tempest\ORM\Attributes\CastWith;
use Tempest\ORM\IsModel;
use Tempest\ORM\Model;

class ObjectFactoryA implements Model
{
    use IsModel;

    #[CastWith(ObjectFactoryACaster::class)]
    public string $prop;
}
