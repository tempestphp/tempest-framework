<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Database\Relations\Fixtures;

use Tempest\Database\BelongsTo;
use Tempest\Database\IsDatabaseModel;
use Tempest\Database\TableName;

#[TableName('belongs_to_parent_model')]
final class BelongsToParentModel
{
    use IsDatabaseModel;

    public BelongsToRelatedModel $relatedModel;

    #[BelongsTo('other_id')]
    public BelongsToRelatedModel $otherRelatedModel;

    #[BelongsTo('other_id', 'other_id')]
    public BelongsToRelatedModel $stillOtherRelatedModel;
}
