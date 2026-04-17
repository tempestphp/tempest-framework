<?php

namespace Tempest\Database\QueryStatements;

use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\Direction;
use Tempest\Database\QueryStatement;

use function Tempest\Support\str;

final readonly class OrderByStatement implements QueryStatement
{
    public function __construct(
        private string $field,
        private Direction $direction = Direction::ASC,
    ) {}

    public function compile(DatabaseDialect $dialect): string
    {
        $quoted = str($this->field)
            ->explode(separator: '.')
            ->map($dialect->quoteIdentifier(...))
            ->implode(glue: '.');

        return "{$quoted} {$this->direction->value}";
    }
}
