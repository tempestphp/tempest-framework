<?php

namespace Tests\Tempest\Integration\ORM\Models;

use Tempest\Database\Builder\TableDefinition;
use Tempest\Database\IsDatabaseModel;
use Tempest\Database\TableName;

#[TableName('custom_static_method_table_name')]
final class StaticMethodTableNameModel
{
    use IsDatabaseModel;
}
