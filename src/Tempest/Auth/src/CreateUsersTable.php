<?php

declare(strict_types=1);

namespace Tempest\Auth;

use Tempest\Database\Migration;
use Tempest\Database\QueryStatements\CreateTableStatement;
use Tempest\Database\QueryStatements\DropTableStatement;

final readonly class CreateUsersTable implements Migration
{
    public function getName(): string
    {
        return '0000-00-00_create_users_table';
    }

    public function up(): CreateTableStatement
    {
        return (new CreateTableStatement('users'))
            ->primary()
            ->varchar('name')
            ->varchar('email')
            ->datetime('emailValidatedAt', nullable: true)
            ->text('password');
    }

    public function down(): DropTableStatement
    {
        return DropTableStatement::forModel(User::class);
    }
}
