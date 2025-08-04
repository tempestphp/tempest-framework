<?php

namespace Tests\Tempest\Integration\ORM\Models;

use Tempest\Database\IsDatabaseModel;
use Tempest\Database\PrimaryKey;

final class BaseModel
{
    use IsDatabaseModel;

    public PrimaryKey $id;
}
