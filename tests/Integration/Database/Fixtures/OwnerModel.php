<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Database\Fixtures;

use Tempest\Database\BelongsTo;
use Tempest\Database\Table;

#[Table('owner')]
final class OwnerModel
{
    public RelationModel $relation;

    #[BelongsTo(relationJoin: 'overwritten_id')]
    public RelationModel $relationJoinField;

    #[BelongsTo(relationJoin: 'overwritten.overwritten_id')]
    public RelationModel $relationJoinFieldAndTable;

    #[BelongsTo(ownerJoin: 'overwritten_id')]
    public RelationModel $ownerJoinField;

    #[BelongsTo(ownerJoin: 'overwritten.overwritten_id')]
    public RelationModel $ownerJoinFieldAndTable;

    public string $name;
}
