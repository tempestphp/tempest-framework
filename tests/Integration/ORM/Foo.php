<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\ORM;

use Tempest\Database\IsModel;
use Tempest\Database\Model;

class Foo implements Model
{
    use IsModel;

    public string $bar;
}
