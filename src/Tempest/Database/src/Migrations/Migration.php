<?php

declare(strict_types=1);

namespace Tempest\Database\Migrations;

use Tempest\Database\IsDatabaseModel;

final class Migration
{
    use IsDatabaseModel;

    public string $name;
}
