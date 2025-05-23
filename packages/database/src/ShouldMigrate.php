<?php

namespace Tempest\Database;

interface ShouldMigrate
{
    public function shouldMigrate(Database $database): bool;
}