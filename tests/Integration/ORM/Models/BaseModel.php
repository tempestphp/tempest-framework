<?php

namespace Tests\Tempest\Integration\ORM\Models;

use Tempest\Database\DatabaseModel;
use Tempest\Database\IsDatabaseModel;

final class BaseModel implements DatabaseModel
{
    use IsDatabaseModel;
}
