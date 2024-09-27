<?php

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
        return CreateTableStatement::forModel(User::class)
            ->primary()
            ->text('name')
            ->text('email')
            ->datetime('emailValidatedAt', nullable: true)
            ->text('password');
    }

    public function down(): DropTableStatement
    {
        return DropTableStatement::forModel(User::class);
    }
}