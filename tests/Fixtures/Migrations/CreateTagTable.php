<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Migrations;

use Tempest\Database\MigratesDown;
use Tempest\Database\MigratesUp;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;
use Tempest\Database\QueryStatements\DropTableStatement;
use Tests\Tempest\Fixtures\Modules\Books\Models\Tag;

final class CreateTagTable implements MigratesUp, MigratesDown
{
    private(set) string $name = '0000-00-10_create_tags_table';

    public function up(): QueryStatement
    {
        return CreateTableStatement::forModel(modelClass: Tag::class)
            ->primary()
            ->text(name: 'label');
    }

    public function down(): QueryStatement
    {
        return DropTableStatement::forModel(modelClass: Tag::class);
    }
}
