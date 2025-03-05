<?php

declare(strict_types=1);

namespace Tempest\Database\QueryStatements;

use Tempest\Database\Builder\TableName;
use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\QueryStatement;
use Tempest\Support\StringHelper;
use function Tempest\Support\arr;
use function Tempest\Support\str;

final class AlterTableStatement implements QueryStatement
{
    public function __construct(
        private readonly string $tableName,
        private array $statements = [],
        private array $createIndexStatements = [],
    ) {
    }

    /** @param class-string<\Tempest\Database\DatabaseModel> $modelClass */
    public static function forModel(string $modelClass): self
    {
        return new self($modelClass::table()->tableName);
    }

    public function add(QueryStatement $statement): self
    {
        $this->statements[] = new AlterStatement(Alter::ADD, $statement);

        return $this;
    }

    public function update(QueryStatement $statement): self
    {
        $this->statements[] = new AlterStatement(Alter::UPDATE, $statement);

        return $this;
    }

    public function delete(string $table): self
    {
        $this->statements[] = new AlterStatement(
            Alter::DELETE,
            new RawStatement($table),
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

    public function unique(string ...$columns): self
    {
        $this->createIndexStatements[] = new UniqueStatement(
            tableName: $this->tableName,
            columns: $columns,
        );

        return $this;
    }

    public function index(string ...$columns): self
    {
        $this->createIndexStatements[] = new IndexStatement(
            tableName: $this->tableName,
            columns: $columns,
        );

        return $this;
    }

    public function drop(QueryStatement $statement): self
    {
        $this->statements[] = new AlterStatement(Alter::DROP, $statement);

        return $this;
    }

    public function compile(DatabaseDialect $dialect): string
    {
        if ($this->statements !== []) {
            $alterTable = sprintf(
                'ALTER TABLE %s %s;',
                new TableName($this->tableName),
                arr($this->statements)
                    ->map(fn (QueryStatement $queryStatement) => str($queryStatement->compile($dialect))->trim()->replace('  ', ' '))
                    ->filter(fn (StringHelper $line) => $line->isNotEmpty())
                    ->implode(', ' . PHP_EOL . '    ')
                    ->wrap(before: PHP_EOL . '    ', after: PHP_EOL)
                    ->toString(),
            );
        } else {
            $alterTable = '';
        }

        if ($this->createIndexStatements !== []) {
            $createIndices = PHP_EOL . arr($this->createIndexStatements)
                    ->map(fn (QueryStatement $queryStatement) => str($queryStatement->compile($dialect))->trim()->replace('  ', ' '))
                    ->implode(';' . PHP_EOL)
                    ->append(';');
        } else {
            $createIndices = '';
        }

        return $alterTable . $createIndices;
    }
}
