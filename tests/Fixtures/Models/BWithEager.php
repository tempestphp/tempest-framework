<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Models;

use Tempest\Database\Builder\TableName;
use Tempest\Database\DatabaseModel;
use Tempest\Database\Eager;
use Tempest\Database\IsDatabaseModel;

final class BWithEager implements DatabaseModel
{
    use IsDatabaseModel;

    public static function table(): TableName
    {
        return new TableName('B');
    }

    public function __construct(
        #[Eager]
        public C $c,
    ) {
    }
}
