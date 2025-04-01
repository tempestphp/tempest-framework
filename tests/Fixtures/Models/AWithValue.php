<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Models;

use Tempest\Database\Builder\TableDefinition;
use Tempest\Database\IsDatabaseModel;
use Tempest\Database\Table;

#[Table('a')]
final class AWithValue
{
    use IsDatabaseModel;

    public function __construct(
        public string $name,
    ) {}
}
