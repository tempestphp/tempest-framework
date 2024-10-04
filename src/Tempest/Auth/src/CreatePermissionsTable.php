<?php

declare(strict_types=1);

namespace Tempest\Auth;

use Tempest\Core\CanBePublished;
use Tempest\Core\DoNotDiscover;
use Tempest\Core\PublishDiscovery;
use Tempest\Database\Migration;
use Tempest\Database\QueryStatements\CreateTableStatement;
use Tempest\Database\QueryStatements\DropTableStatement;

#[CanBePublished]
#[DoNotDiscover(except: [PublishDiscovery::class])]
final readonly class CreatePermissionsTable implements Migration
{
    public function getName(): string
    {
        return '0000-00-01_create_permissions_table';
    }

    public function up(): CreateTableStatement
    {
        return (new CreateTableStatement('permissions'))
            ->primary()
            ->varchar('name');
    }

    public function down(): DropTableStatement
    {
        return DropTableStatement::forModel(Permission::class);
    }
}
