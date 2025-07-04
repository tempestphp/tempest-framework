<?php

namespace Tempest\Database;

use UnitEnum;

interface DatabaseSeeder
{
    public function run(null|string|UnitEnum $database): void;
}