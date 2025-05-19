<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\ORM\Models;

use Tempest\Database\BelongsTo;
use Tempest\Database\IsDatabaseModel;
use Tempest\Database\Table;

#[Table('through')]
final class ThroughModel
{
    use IsDatabaseModel;

    public function __construct(
        public ParentModel $parent,
        public ChildModel $child,
        #[BelongsTo(ownerJoin: 'child2_id')]
        public ?ChildModel $child2 = null,
    ) {}
}
