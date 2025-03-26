<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Database\Relations\Fixtures;

use Tempest\Database\DatabaseModel;
use Tempest\Database\IsDatabaseModel;
use Tempest\Database\TableName;

#[TableName('has_one_related')]
final class HasOneRelatedModel implements DatabaseModel
{
    use IsDatabaseModel;

    public function __construct(
        public HasOneParentModel $parent,
        public HasOneParentModel $otherParent,
    ) {}
}
