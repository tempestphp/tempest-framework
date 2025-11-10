<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Migrations;

use Tempest\Database\MigratesDown;
use Tempest\Database\MigratesUp;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;
use Tempest\Database\QueryStatements\DropTableStatement;
use Tests\Tempest\Fixtures\Models\ProfileWithEager;

final class CreateProfileWithEagerTable implements MigratesUp, MigratesDown
{
    private(set) string $name = '0000-00-06_create_profiles_table';

    public function up(): QueryStatement
    {
        return CreateTableStatement::forModel(ProfileWithEager::class)
            ->primary()
            ->text('bio')
            ->belongsTo('profiles.user_id', 'users.id', nullable: true);
    }

    public function down(): QueryStatement
    {
        return DropTableStatement::forModel(ProfileWithEager::class);
    }
}
