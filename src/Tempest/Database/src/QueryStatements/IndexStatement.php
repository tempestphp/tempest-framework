<?php

declare(strict_types=1);

namespace Tempest\Database\QueryStatements;

use Tempest\Database\DatabaseDialect;
use Tempest\Database\QueryStatement;

final readonly class IndexStatement implements QueryStatement
{
    public function __construct(
        private string $indexName,
    ) {
    }

    public function compile(DatabaseDialect $dialect): string
    {
        return sprintf('INDEX %s', $this->indexName);
    }
}
