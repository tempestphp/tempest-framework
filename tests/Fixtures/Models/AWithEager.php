<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Models;

use Tempest\Database\Builder\TableName;
use Tempest\Database\DatabaseModel;
use Tempest\Database\Eager;
use Tempest\Database\IsDatabaseModel;

final class AWithEager implements DatabaseModel
{
    use IsDatabaseModel;

    public static function table(): TableName
    {
        return new TableName('A');
    }

    public function __construct(
        #[Eager]
        public BWithEager $b,
    ) {
    }
}
