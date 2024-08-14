<?php

declare(strict_types=1);

namespace Tempest\Database\QueryStatements;

use Tempest\Database\DatabaseDialect;
use Tempest\Database\QueryStatement;

final readonly class CreateTableStatement implements QueryStatement
{
    public function __construct(
        private string $tableName,
        private array $statements,
    ) {
    }

    public function compile(DatabaseDialect $dialect): string
    {
        return sprintf(
            'CREATE TABLE %s (%s)',
            $this->tableName,
            implode(
                ',',
                array_filter(
                    array_map(
                        fn (QueryStatement $queryStatement) => $queryStatement->compile($dialect),
                        $this->statements,
                    ),
                ),
            ),
        );
    }
}
