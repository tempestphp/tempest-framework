<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\ORM\Models;

use Tempest\Database\Builder\TableDefinition;
use Tempest\Database\DatabaseModel;
use Tempest\Database\IsDatabaseModel;
use Tempest\Database\TableName;

#[TableName('parent')]
final class ParentModel implements DatabaseModel
{
    use IsDatabaseModel;

    public function __construct(
        public string $name,

        /** @var \Tests\Tempest\Integration\ORM\Models\ThroughModel[] */
        public array $through = [],
    ) {}
}
