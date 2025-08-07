<?php

declare(strict_types=1);

namespace Tempest\Database\Tests\QueryStatements;

use Tempest\Database\IsDatabaseModel;
use Tempest\Database\PrimaryKey;

final class StubModel
{
    use IsDatabaseModel;

    public PrimaryKey $id;
}
