<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Database\Relations\Fixtures;

use Tempest\Database\DatabaseModel;
use Tempest\Database\IsDatabaseModel;
use Tempest\Database\TableName;

#[TableName('has_one_invalid_related')]
final class HasOneInvalidRelatedModel implements DatabaseModel
{
    use IsDatabaseModel;

    public function __construct(
        public self $invalidType,
    ) {}
}
