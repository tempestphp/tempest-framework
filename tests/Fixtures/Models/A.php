<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Models;

use Tempest\Database\Builder\TableDefinition;
use Tempest\Database\IsDatabaseModel;
use Tempest\Database\PrimaryKey;

#[\Tempest\Database\Table('a')]
final class A
{
    use IsDatabaseModel;

    public PrimaryKey $id;

    public function __construct(
        public B $b,
    ) {}
}
