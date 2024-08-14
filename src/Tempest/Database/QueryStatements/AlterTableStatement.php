<?php

declare(strict_types=1);

namespace Tempest\Database\QueryStatements;

use Tempest\Database\DatabaseDialect;
use Tempest\Database\QueryStatement;

final class AlterTableStatement implements QueryStatement
{
    public function __construct(
        private readonly string $tableName,
        private array $statements = [],
    ) {
    }

    public function add(QueryStatement $statement): self
    {
        $this->statements[] = new AlterActionStatement(AlterStatement::ADD, $statement);

        return $this;
    }

    public function update(QueryStatement $statement): self
    {
        $this->statements[] = new AlterActionStatement(AlterStatement::UPDATE, $statement);

        return $this;
    }

    public function delete(QueryStatement $statement): self
    {
        $this->statements[] = new AlterActionStatement(AlterStatement::DELETE, $statement);

        return $this;
    }

    public function compile(DatabaseDialect $dialect): string
    {
        return sprintf(
            'ALTER TABLE %s %s;',
            $this->tableName,
            implode(
                ' ',
                array_filter(
                    array_map(
                        static fn (QueryStatement $statement) => $statement->compile($dialect),
                        $this->statements,
                    ),
                ),
            ),
        );
    }
}
