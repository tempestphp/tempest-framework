<?php

declare(strict_types=1);

namespace Tempest\Database\Tests\QueryStatements;

use Tempest\Database\DatabaseModel;
use Tempest\Database\IsDatabaseModel;

final class StubModel implements DatabaseModel
{
    use IsDatabaseModel;
}
