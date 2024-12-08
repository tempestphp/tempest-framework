<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\ORM\Migrations;

use Tempest\Database\Migration;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;
use Tempest\Database\QueryStatements\DropTableStatement;
use Tempest\Database\QueryStatements\PrimaryKeyStatement;
use Tempest\Database\QueryStatements\RawStatement;

final readonly class CreateBTable implements Migration
{
    public function getName(): string
    {
        return '100-create-b';
    }

    public function up(): QueryStatement
    {
        return new CreateTableStatement(
            'b',
            [
                new PrimaryKeyStatement(),
                new RawStatement('c_id INTEGER'),
            ],
        );
    }

    public function down(): QueryStatement
    {
        return new DropTableStatement('b');
    }
}
