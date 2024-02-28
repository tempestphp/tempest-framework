<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\ORM\Fixtures;

use Tempest\ORM\IsModel;
use Tempest\ORM\Model;

class Foo implements Model
{
    use IsModel;

    public string $bar;
}
