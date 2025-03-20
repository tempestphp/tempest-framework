<?php

declare(strict_types=1);

namespace Tempest\Database\Migrations;

use Tempest\Database\DatabaseModel;
use Tempest\Database\IsDatabaseModel;

final class Migration implements DatabaseModel
{
    use IsDatabaseModel;

    public string $name;

    public string $hash;
}
