<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Models;

use Tempest\Database\Builder\TableDefinition;
use Tempest\Database\DatabaseModel;
use Tempest\Database\IsDatabaseModel;
use Tempest\Database\Lazy;

#[\Tempest\Database\TableName('a')]
final class AWithLazy implements DatabaseModel
{
    use IsDatabaseModel;

    public function __construct(
        #[Lazy]
        public B $b,
    ) {}
}
