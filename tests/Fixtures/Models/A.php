<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Models;

use Tempest\Database\Builder\TableDefinition;
use Tempest\Database\DatabaseModel;
use Tempest\Database\IsDatabaseModel;

#[\Tempest\Database\TableName('a')]
final class A implements DatabaseModel
{
    use IsDatabaseModel;

    public function __construct(
        public B $b,
    ) {}
}
