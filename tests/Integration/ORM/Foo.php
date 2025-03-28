<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\ORM;

use Tempest\Database\IsDatabaseModel;

final class Foo
{
    use IsDatabaseModel;

    public string $bar;
}
