<?php

declare(strict_types=1);

namespace Tempest\Database\Tests\Builder\Fixtures;

use Tempest\Database\DatabaseModel;
use Tempest\Database\IsDatabaseModel;

final class BarModel implements DatabaseModel
{
    use IsDatabaseModel;
}
