<?php

declare(strict_types=1);

namespace Tempest\Database\QueryStatements;

use Tempest\Database\Builder\TableName;
use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\QueryStatement;
use Tempest\Support\Str\ImmutableString;

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

    public function drop(ColumnNameStatement|ConstraintNameStatement $statement): self
    {
        $this->statements[] = new AlterStatement(Alter::DROP, $statement);

        return $this;
    }

    public function rename(RenameColumnStatement $rename): self
    {
        $this->statements[] = new AlterStatement(Alter::RENAME, $rename);

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
                    ->filter(fn (ImmutableString $line) => $line->isNotEmpty())
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
