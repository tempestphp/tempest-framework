<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Models;

use Tempest\Database\Builder\TableDefinition;
use Tempest\Database\IsDatabaseModel;
use Tempest\Database\Table;
use Tempest\Database\Virtual;

#[Table('a')]
final class AWithVirtual
{
    use IsDatabaseModel;

    #[Virtual]
    public int $fake {
        get => -$this->id->id;
    }

    public function __construct(
        public B $b,
    ) {}
}
