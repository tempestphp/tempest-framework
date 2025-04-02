<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Database\Relations\Fixtures;

use Tempest\Database\IsDatabaseModel;
use Tempest\Database\Table;

#[Table('has_one_invalid_related')]
final class HasOneInvalidRelatedModel
{
    use IsDatabaseModel;

    public function __construct(
        public self $invalidType,
    ) {}
}
