<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Models;

use Tempest\Database\Builder\TableDefinition;
use Tempest\Database\IsDatabaseModel;
use Tempest\Database\Lazy;
use Tempest\Database\PrimaryKey;

#[\Tempest\Database\Table('a')]
final class AWithLazy
{
    use IsDatabaseModel;

    public function __construct(
        #[Lazy]
        public B $b,
    ) {}
}
