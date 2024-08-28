<?php

declare(strict_types=1);

namespace Tempest\Database\QueryStatements;

use Tempest\Database\Builder\TableName;
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

    public function delete(string $table): self
    {
        $this->statements[] = new AlterActionStatement(
            AlterStatement::DELETE,
            new RawStatement($table)
        );

        return $this;
    }

    public function constraint(string $constraintName, ?QueryStatement $statement = null): self
    {
        $this->statements[] = new ConstraintStatement($constraintName, $statement);

        if ($statement !== null) {
            $this->statements[] = $statement;
        }

        return $this;
    }

    public function unique(string $columnName): self
    {
        $this->statements[] = new UniqueStatement($columnName);

        return $this;
    }

    public function index(string $indexName): self
    {
        $this->statements[] = new IndexStatement($indexName);

        return $this;
    }

    public function drop(QueryStatement $statement): self
    {
        $this->statements[] = new AlterActionStatement(AlterStatement::DROP, $statement);

        return $this;
    }

    public function compile(DatabaseDialect $dialect): string
    {
        return sprintf(
            'ALTER TABLE %s %s;',
            new TableName($this->tableName),
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
