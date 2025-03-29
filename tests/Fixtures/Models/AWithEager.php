<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Models;

use Tempest\Database\Builder\TableDefinition;
use Tempest\Database\Eager;
use Tempest\Database\IsDatabaseModel;

#[\Tempest\Database\Table('a')]
final class AWithEager
{
    use IsDatabaseModel;

    public function __construct(
        #[Eager]
        public BWithEager $b,
    ) {}
}
