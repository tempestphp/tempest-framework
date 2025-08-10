<?php

declare(strict_types=1);

namespace Tempest\Auth\Install;

use Tempest\Database\MigratesDown;
use Tempest\Database\MigratesUp;
use Tempest\Database\QueryStatements\CreateTableStatement;
use Tempest\Database\QueryStatements\DropTableStatement;
use Tempest\Discovery\SkipDiscovery;

#[SkipDiscovery]
final class CreatePermissionsTable implements MigratesUp, MigratesDown
{
    private(set) string $name = '0000-00-01_create_permissions_table';

    public function up(): CreateTableStatement
    {
        return new CreateTableStatement('permissions')
            ->primary()
            ->varchar('name');
    }

    public function down(): DropTableStatement
    {
        return new DropTableStatement('permissions');
    }
}
