<?php

declare(strict_types=1);

namespace Tempest\Database\QueryStatements;

use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\QueryStatement;

use function Tempest\Support\arr;

final readonly class CompoundStatement implements QueryStatement
{
    private array $statements;

    public function __construct(QueryStatement ...$statements)
    {
        $this->statements = $statements;
    }

    public function compile(DatabaseDialect $dialect): string
    {
        return arr($this->statements)
            ->map(fn (QueryStatement $statement) => $statement->compile($dialect))
            ->implode(';' . PHP_EOL)
            ->append(';')
            ->toString();
    }
}
