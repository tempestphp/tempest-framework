<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Models;

use Tempest\Database\Builder\TableName;
use Tempest\Database\DatabaseModel;
use Tempest\Database\IsDatabaseModel;

final class A implements DatabaseModel
{
    use IsDatabaseModel;

    public function __construct(
        public B $b,
    ) {}

    public static function table(): TableName
    {
        return new TableName('a');
    }
}
