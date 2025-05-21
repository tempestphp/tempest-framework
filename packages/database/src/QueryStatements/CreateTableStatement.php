<?php

declare(strict_types=1);

namespace Tempest\Database\QueryStatements;

use BackedEnum;
use Tempest\Database\Builder\ModelDefinition;
use Tempest\Database\Builder\TableDefinition;
use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\QueryStatement;
use Tempest\Support\Str\ImmutableString;
use UnitEnum;

use function Tempest\Support\arr;
use function Tempest\Support\str;

final class CreateTableStatement implements QueryStatement
{
    public function __construct(
        private readonly string $tableName,
        private array $statements = [],
        private array $indexStatements = [],
    ) {}

    /** @param class-string $modelClass */
    public static function forModel(string $modelClass): self
    {
        return new self(new ModelDefinition($modelClass)->getTableDefinition()->name);
    }

    public function primary(string $name = 'id'): self
    {
        $this->statements[] = new PrimaryKeyStatement($name);

        return $this;
    }

    public function belongsTo(
        string $local,
        string $foreign,
        OnDelete $onDelete = OnDelete::RESTRICT,
        OnUpdate $onUpdate = OnUpdate::NO_ACTION,
        bool $nullable = false,
    ): self {
        [, $localKey] = explode('.', $local);

        $this->integer($localKey, nullable: $nullable);

        $this->statements[] = new BelongsToStatement(
            local: $local,
            foreign: $foreign,
            onDelete: $onDelete,
            onUpdate: $onUpdate,
        );

        return $this;
    }

    public function text(
        string $name,
        bool $nullable = false,
        ?string $default = null,
    ): self {
        $this->statements[] = new TextStatement(
            name: $name,
            nullable: $nullable,
            default: $default,
        );

        return $this;
    }

    public function varchar(
        string $name,
        int $length = 255,
        bool $nullable = false,
        ?string $default = null,
    ): self {
        $this->statements[] = new VarcharStatement(
            name: $name,
            size: $length,
            nullable: $nullable,
            default: $default,
        );

        return $this;
    }

    public function char(
        string $name,
        bool $nullable = false,
        ?string $default = null,
    ): self {
        $this->statements[] = new CharStatement(
            name: $name,
            nullable: $nullable,
            default: $default,
        );

        return $this;
    }

    public function integer(
        string $name,
        bool $unsigned = false,
        bool $nullable = false,
        ?int $default = null,
    ): self {
        $this->statements[] = new IntegerStatement(
            name: $name,
            unsigned: $unsigned,
            nullable: $nullable,
            default: $default,
        );

        return $this;
    }

    public function float(
        string $name,
        bool $nullable = false,
        ?float $default = null,
    ): self {
        $this->statements[] = new FloatStatement(
            name: $name,
            nullable: $nullable,
            default: $default,
        );

        return $this;
    }

    public function datetime(
        string $name,
        bool $nullable = false,
        ?string $default = null,
    ): self {
        $this->statements[] = new DatetimeStatement(
            name: $name,
            nullable: $nullable,
            default: $default,
        );

        return $this;
    }

    public function date(
        string $name,
        bool $nullable = false,
        ?string $default = null,
    ): self {
        $this->statements[] = new DateStatement(
            name: $name,
            nullable: $nullable,
            default: $default,
        );

        return $this;
    }

    public function boolean(
        string $name,
        bool $nullable = false,
        ?bool $default = null,
    ): self {
        $this->statements[] = new BooleanStatement(
            name: $name,
            nullable: $nullable,
            default: $default,
        );

        return $this;
    }

    public function json(
        string $name,
        bool $nullable = false,
        ?string $default = null,
    ): self {
        $this->statements[] = new JsonStatement(
            name: $name,
            nullable: $nullable,
            default: $default,
        );

        return $this;
    }

    public function array(
        string $name,
        bool $nullable = false,
        array $default = [],
    ): self {
        $this->statements[] = new JsonStatement(
            name: $name,
            nullable: $nullable,
            default: json_encode($default),
        );

        return $this;
    }

    public function enum(
        string $name,
        string $enumClass,
        bool $nullable = false,
        null|UnitEnum|BackedEnum $default = null,
    ): self {
        $this->statements[] = new EnumStatement(
            name: $name,
            enumClass: $enumClass,
            nullable: $nullable,
            default: $default,
        );

        return $this;
    }

    public function set(
        string $name,
        array $values,
        bool $nullable = false,
        ?string $default = null,
    ): self {
        $this->statements[] = new SetStatement(
            name: $name,
            values: $values,
            nullable: $nullable,
            default: $default,
        );

        return $this;
    }

    public function unique(string ...$columns): self
    {
        $this->indexStatements[] = new UniqueStatement(
            tableName: $this->tableName,
            columns: $columns,
        );

        return $this;
    }

    public function index(string ...$columns): self
    {
        $this->indexStatements[] = new IndexStatement(
            tableName: $this->tableName,
            columns: $columns,
        );

        return $this;
    }

    public function raw(string $statement): self
    {
        $this->statements[] = new RawStatement($statement);

        return $this;
    }

    public function compile(DatabaseDialect $dialect): string
    {
        $createTable = sprintf(
            'CREATE TABLE %s (%s);',
            new TableDefinition($this->tableName),
            arr($this->statements)
                // Remove BelongsTo for sqlLite as it does not support those queries
                ->filter(fn (QueryStatement $queryStatement) => ! ($dialect === DatabaseDialect::SQLITE && $queryStatement instanceof BelongsToStatement))
                ->map(fn (QueryStatement $queryStatement) => str($queryStatement->compile($dialect))->trim()->replace('  ', ' '))
                ->filter(fn (ImmutableString $str) => $str->isNotEmpty())
                ->implode(', ' . PHP_EOL . '    ')
                ->wrap(before: PHP_EOL . '    ', after: PHP_EOL)
                ->toString(),
        );

        if ($this->indexStatements !== []) {
            $createIndices = PHP_EOL . arr($this->indexStatements)
                ->map(fn (QueryStatement $queryStatement) => str($queryStatement->compile($dialect))->trim()->replace('  ', ' '))
                ->implode(';' . PHP_EOL)
                ->append(';');
        } else {
            $createIndices = '';
        }

        return $createTable . $createIndices;
    }
}
