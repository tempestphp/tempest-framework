<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\ORM\Models;

use Tempest\Database\Builder\TableName;
use Tempest\Database\DatabaseModel;
use Tempest\Database\IsDatabaseModel;

final class ParentModel implements DatabaseModel
{
    use IsDatabaseModel;

    public static function table(): TableName
    {
        return new TableName('parent');
    }

    public function __construct(
        public string $name,

        /** @var \Tests\Tempest\Integration\ORM\Models\ThroughModel[] */
        public array $through = [],
    ) {}
}
