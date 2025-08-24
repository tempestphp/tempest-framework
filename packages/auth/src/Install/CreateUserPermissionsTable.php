<?php

declare(strict_types=1);

namespace Tempest\Auth\Install;

use Tempest\Database\MigratesDown;
use Tempest\Database\MigratesUp;
use Tempest\Database\QueryStatements\CreateTableStatement;
use Tempest\Database\QueryStatements\DropTableStatement;
use Tempest\Discovery\SkipDiscovery;

#[SkipDiscovery]
final class CreateUserPermissionsTable implements MigratesUp, MigratesDown
{
    private(set) string $name = '0000-00-02_create_user_permissions_table';

    public function up(): CreateTableStatement
    {
        return new CreateTableStatement('user_permissions')
            ->primary()
            ->belongsTo('user_permissions.user_id', 'users.id')
            ->belongsTo('user_permissions.permission_id', 'permissions.id');
    }

    public function down(): DropTableStatement
    {
        return new DropTableStatement('user_permissions');
    }
}
