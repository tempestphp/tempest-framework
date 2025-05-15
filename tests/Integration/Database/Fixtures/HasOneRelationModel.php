<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Database\Fixtures;

use Tempest\Database\HasOne;
use Tempest\Database\Table;

#[Table('relation')]
final class HasOneRelationModel
{
    #[HasOne]
    public OwnerModel $owner;

    #[HasOne(ownerJoin: 'overwritten_id')]
    public OwnerModel $ownerJoinField;

    #[HasOne(ownerJoin: 'overwritten.overwritten_id')]
    public OwnerModel $ownerJoinFieldAndTable;

    #[HasOne(relationJoin: 'overwritten_id')]
    public OwnerModel $relationJoinField;

    #[HasOne(relationJoin: 'overwritten.overwritten_id')]
    public OwnerModel $relationJoinFieldAndTable;
}
