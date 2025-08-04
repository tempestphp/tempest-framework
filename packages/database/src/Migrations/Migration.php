<?php

declare(strict_types=1);

namespace Tempest\Database\Migrations;

use Tempest\Database\IsDatabaseModel;
use Tempest\Database\PrimaryKey;

final class Migration
{
    use IsDatabaseModel;

    public PrimaryKey $id;

    public string $name;

    public string $hash;
}
