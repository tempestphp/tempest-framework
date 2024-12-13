<?php

declare(strict_types=1);

namespace Tempest\Database\QueryStatements;

use Tempest\Database\DatabaseDialect;
use Tempest\Database\QueryStatement;
use function Tempest\Support\arr;

final readonly class IndexStatement implements QueryStatement
{
    public function __construct(
        private string $tableName,
        private array $columns,
    ) {
    }

    public function compile(DatabaseDialect $dialect): string
    {
        $columns = arr($this->columns)->implode('`, `')->wrap('`', '`');

        $on = sprintf('(%s)', $columns);

        return sprintf('INDEX %s', $on);
    }
}
