<?php

namespace Tests\Tempest\Integration\ORM\Models;

use Tempest\Database\Builder\TableName;
use Tempest\Database\DatabaseModel;
use Tempest\Database\IsDatabaseModel;

final class StaticMethodTableNameModel implements DatabaseModel
{
    use IsDatabaseModel;

    public static function table(): TableName
    {
        return new TableName('custom_static_method_table_name');
    }
}
