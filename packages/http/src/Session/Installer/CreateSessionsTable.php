<?php

declare(strict_types=1);

namespace Tempest\Http\Session\Installer;

use Tempest\Database\MigratesUp;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;
use Tempest\Discovery\SkipDiscovery;

#[SkipDiscovery]
final class CreateSessionsTable implements MigratesUp
{
    private(set) string $name = '0000-00-00_create_sessions_table';

    public function up(): QueryStatement
    {
        return new CreateTableStatement('sessions')
            ->primary('id')
            ->string('session_id')
            ->text('data')
            ->datetime('created_at')
            ->datetime('last_active_at')
            ->index('session_id');
    }
}
