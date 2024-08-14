<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\ORM\Migrations;

use Tempest\Database\Migration;
use Tempest\Database\QueryStatements\CreateTableStatement;
use Tempest\Database\QueryStatements\DropTableStatement;
use Tempest\Database\QueryStatements\PrimaryKeyStatement;
use Tempest\Database\QueryStatements\TextStatement;

final readonly class CreateCTable implements Migration
{
    public function getName(): string
    {
        return '100-create-c';
    }

    public function up(): CreateTableStatement|null
    {
        return new CreateTableStatement('C', [
            new PrimaryKeyStatement(),
            new TextStatement('name'),
        ]);
    }

    public function down(): DropTableStatement|null
    {
        return new DropTableStatement('C');
    }
}
