<?php

declare(strict_types=1);

namespace Tempest\Database\QueryStatements;

use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\QueryStatement;

use function Tempest\Support\arr;
use function Tempest\Support\str;

final readonly class UniqueStatement implements QueryStatement
{
    public function __construct(
        private string $tableName,
        private array $columns,
    ) {}

    public function compile(DatabaseDialect $dialect): string
    {
        $columns = arr($this->columns)
            ->map($dialect->quoteIdentifier(...))
            ->implode(', ');

        $rawColumns = arr($this->columns)->implode('_');
        $indexName = str($this->tableName . '_' . $rawColumns)->snake()->toString();

        $on = sprintf('%s (%s)', $dialect->quoteIdentifier($this->tableName), $columns);

        return sprintf('CREATE UNIQUE INDEX %s ON %s', $dialect->quoteIdentifier($indexName), $on);
    }
}
