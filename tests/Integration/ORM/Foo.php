<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\ORM;

use Tempest\Database\DatabaseModel;
use Tempest\Database\IsDatabaseModel;

final class Foo implements DatabaseModel
{
    use IsDatabaseModel;

    public string $bar;
}
