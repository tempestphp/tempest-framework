<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Models;

use Tempest\Database\Builder\TableName;
use Tempest\Database\DatabaseModel;
use Tempest\Database\IsDatabaseModel;
use Tempest\Database\Lazy;

final class AWithLazy implements DatabaseModel
{
    use IsDatabaseModel;

    public static function table(): TableName
    {
        return new TableName('A');
    }

    public function __construct(
        #[Lazy]
        public B $b,
    ) {
    }
}
