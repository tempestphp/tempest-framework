<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Migrations;

use Tempest\Database\Migration;
use Tempest\Database\QueryStatements\CreateTableStatement;
use Tempest\Database\QueryStatements\DropTableStatement;
use Tempest\Database\QueryStatements\PrimaryKeyStatement;
use Tempest\Database\QueryStatements\TextStatement;

final readonly class CreateAuthorTable implements Migration
{
    public function getName(): string
    {
        return '0000-00-00_create_author_table';
    }

    public function up(): CreateTableStatement|null
    {
        return new CreateTableStatement(
            'Author',
            [
                new PrimaryKeyStatement(),
                new TextStatement('name'),
                new TextStatement('type', nullable: true),
            ]
        );
    }

    public function down(): DropTableStatement|null
    {
        return new DropTableStatement('Author');
    }
}
