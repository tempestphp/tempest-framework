<?php

declare(strict_types=1);

namespace Tempest\Database\Migrations;

final class DatePrefixStrategy implements MigrationNamingStrategy
{
    public function generatePrefix(): string
    {
        return date('Y-m-d');
    }
}
