<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Database\Relations\Fixtures;

use Tempest\Database\BelongsTo;
use Tempest\Database\IsDatabaseModel;
use Tempest\Database\Table;

#[Table('owner')]
final class BelongsToOwnerModel
{
    use IsDatabaseModel;

    public BelongsToRelationModel $relatedModel;

    #[BelongsTo(relationJoin: 'overwritten_id')]
    public BelongsToRelationModel $relationJoinField;

    #[BelongsTo(relationJoin: 'overwritten.overwritten_id')]
    public BelongsToRelationModel $relationJoinFieldAndTable;

    #[BelongsTo(ownerJoin: 'overwritten_id')]
    public BelongsToRelationModel $ownerJoinField;

    #[BelongsTo(ownerJoin: 'overwritten.overwritten_id')]
    public BelongsToRelationModel $ownerJoinFieldAndTable;
}
