<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Models;

use Tempest\Database\DatabaseModel;
use Tempest\Database\IsDatabaseModel;

final class MultiWordModel implements DatabaseModel
{
    use IsDatabaseModel;
}
