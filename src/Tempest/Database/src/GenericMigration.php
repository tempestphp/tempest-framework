<?php

declare(strict_types=1);

namespace Tempest\Database;

use Tempest\Database\QueryStatements\RawStatement;

final readonly class GenericMigration implements Migration
{
    public function __construct(
        private string $fileName,
        private string $content,
    ) {
    }

    public function getName(): string
    {
        return $this->fileName;
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
