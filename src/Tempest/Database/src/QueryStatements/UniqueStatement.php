<?php

declare(strict_types=1);

namespace Tempest\Database\QueryStatements;

use Tempest\Database\DatabaseDialect;
use Tempest\Database\QueryStatement;
use function Tempest\Support\arr;
use function Tempest\Support\str;

final readonly class UniqueStatement implements QueryStatement
{
    public function __construct(
        private string $tableName,
        private array $columns,
    ) {
    }

    public function compile(DatabaseDialect $dialect): string
    {
        $columns = arr($this->columns)->implode('`, `')->wrap('`', '`');

        $indexName = str($this->tableName . ' ' . $columns->replace(',', '')->snake())->snake()->toString();

        $on = sprintf('`%s` (%s)', $this->tableName, $columns);

        return sprintf('CREATE UNIQUE INDEX `%s` ON %s', $indexName, $on);
    }
}
