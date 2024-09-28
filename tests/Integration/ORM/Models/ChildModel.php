<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\ORM\Models;

use Tempest\Database\Builder\TableName;
use Tempest\Database\DatabaseModel;
use Tempest\Database\IsDatabaseModel;

final class ChildModel implements DatabaseModel
{
    use IsDatabaseModel;

    public ThroughModel $through;

    public static function table(): TableName
    {
        return new TableName('child');
    }

    public function __construct(
        public string $name,
    ) {
    }
}
