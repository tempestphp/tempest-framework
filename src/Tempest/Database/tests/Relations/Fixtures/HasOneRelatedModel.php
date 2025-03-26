<?php

declare(strict_types=1);

namespace Tempest\Database\Tests\Relations\Fixtures;

use Tempest\Database\Builder\TableDefinition;
use Tempest\Database\DatabaseModel;
use Tempest\Database\IsDatabaseModel;

final class HasOneRelatedModel implements DatabaseModel
{
    use IsDatabaseModel;

    public function __construct(
        public HasOneParentModel $parent,
        public HasOneParentModel $otherParent,
    ) {}

    public static function table(): TableDefinition
    {
        return new TableDefinition('has_one_related');
    }
}
