<?php

namespace Tempest\Database;

use UnitEnum;

interface DatabaseSeeder
{
    public function run(string|UnitEnum|null $database): void;
}
