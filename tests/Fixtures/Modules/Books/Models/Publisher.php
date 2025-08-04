<?php

namespace Tests\Tempest\Fixtures\Modules\Books\Models;

use Tempest\Database\PrimaryKey;

final class Publisher
{
    public PrimaryKey $id;

    public string $name;

    public string $description;
}
