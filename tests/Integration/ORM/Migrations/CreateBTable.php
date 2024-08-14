<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\ORM\Migrations;

use Tempest\Database\Migration;
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

    public function up(): CreateTableStatement|null
    {
        return new CreateTableStatement(
            'B',
            [
                new PrimaryKeyStatement(),
                new RawStatement('c_id INTEGER'),
            ]
        );
    }

    public function down(): DropTableStatement|null
    {
        return new DropTableStatement('B');
    }
}
