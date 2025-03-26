<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\ORM\Models;

use Tempest\Database\Builder\TableDefinition;
use Tempest\Database\IsDatabaseModel;
use Tempest\Database\TableName;

#[TableName('through')]
final class ThroughModel
{
    use IsDatabaseModel;

    public function __construct(
        public ParentModel $parent,
        public ChildModel $child,
        public ?ChildModel $child2 = null,
    ) {}
}
