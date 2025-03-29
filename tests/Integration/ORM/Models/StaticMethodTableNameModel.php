<?php

namespace Tests\Tempest\Integration\ORM\Models;

use Tempest\Database\IsDatabaseModel;
use Tempest\Database\Table;

#[Table('custom_static_method_table_name')]
final class StaticMethodTableNameModel
{
    use IsDatabaseModel;
}
