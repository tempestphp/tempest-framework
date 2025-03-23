<?php

declare(strict_types=1);

namespace Tempest\Database\Tests\Relations\Fixtures;

use Tempest\Database\Builder\TableName;
use Tempest\Database\DatabaseModel;
use Tempest\Database\IsDatabaseModel;

final class HasOneRelatedModel implements DatabaseModel
{
    use IsDatabaseModel;

    public function __construct(
        public HasOneParentModel $parent,
        public HasOneParentModel $otherParent,
    ) {}

    public static function table(): TableName
    {
        return new TableName('has_one_related');
    }
}
