<?php

namespace Tests\Tempest\Integration\ORM\Models;

use Tempest\Database\DatabaseModel;
use Tempest\Database\IsDatabaseModel;
use Tempest\Database\TableName;

#[TableName('custom_attribute_table_name')]
final class AttributeTableNameModel implements DatabaseModel
{
    use IsDatabaseModel;
}
