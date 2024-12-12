<?php

declare(strict_types=1);

namespace Tempest\Database;

use Tempest\Database\QueryStatements\RawStatement;

final class GenericDatabaseMigration implements DatabaseMigration
{
    private(set) public string $name;

    public function __construct(
        private string $fileName,
        private string $content,
    ) {
        $this->name = $this->fileName;
    }

    public function up(): QueryStatement
    {
        return new RawStatement($this->content);
    }

    public function down(): QueryStatement|null
    {
        return null;
    }
}
