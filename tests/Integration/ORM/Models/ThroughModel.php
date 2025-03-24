<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\ORM\Models;

use Tempest\Database\Builder\TableName;
use Tempest\Database\DatabaseModel;
use Tempest\Database\IsDatabaseModel;

final class ThroughModel implements DatabaseModel
{
    use IsDatabaseModel;

    public static function table(): TableName
    {
        return new TableName('through');
    }

    public function __construct(
        public ParentModel $parent,
        public ChildModel $child,
        public ?ChildModel $child2 = null,
    ) {}
}
