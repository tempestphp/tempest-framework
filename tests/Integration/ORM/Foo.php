<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\ORM;

use Tempest\ORM\IsModel;
use Tempest\ORM\Model;

class Foo implements Model
{
    use IsModel;

    public string $bar;
}
