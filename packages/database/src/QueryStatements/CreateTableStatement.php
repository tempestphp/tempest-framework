<?php

declare(strict_types=1);

namespace Tempest\Database\QueryStatements;

use BackedEnum;
use Tempest\Database\Builder\TableDefinition;
use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\HasTrailingStatements;
use Tempest\Database\QueryStatement;
use Tempest\Support\Json;
use Tempest\Support\Str\ImmutableString;
use UnitEnum;

use function Tempest\Database\inspect;
use function Tempest\Support\arr;
use function Tempest\Support\str;

final class CreateTableStatement implements QueryStatement, HasTrailingStatements
{
    private(set) array $trailingStatements = [];

    public function __construct(
        private readonly string $tableName,
        private array $statements = [],
    ) {}

    /** @param class-string $modelClass */
    public static function forModel(string $modelClass): self
    {
        return new self(inspect($modelClass)->getTableDefinition()->name);
    }

    /**
     * Adds a primary key column to the table. MySQL and SQLite use an auto-incrementing `INTEGER` column, and PostgreSQL uses `SERIAL`.
     */
    public function primary(string $name = 'id'): self
    {
        $this->statements[] = new PrimaryKeyStatement($name);

        return $this;
    }

    /**
     * Adds an integer column with a foreign key relationship to another table. This is an alias to `foreignId`.
     *
     * **Example**
     * ```php
     * $table->belongsTo('orders.customer_id', 'customers.id');
     * ```
     *
     * @param string $local The local column in the format `this_table.foreign_id`.
     * @param string $foreign The foreign column in the format `other_table.id`.
     */
    public function belongsTo(string $local, string $foreign, OnDelete $onDelete = OnDelete::RESTRICT, OnUpdate $onUpdate = OnUpdate::NO_ACTION, bool $nullable = false): self
    {
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

    /**
     * Adds an integer column with a foreign key relationship to another table.
     *
     * **Example**
     * ```php
     * new CreateTableStatement('orders')
     *   ->foreignId('customer_id', constrainedOn: 'customers');
     * ```
     * ```php
     * new CreateTableStatement('orders')
     *   ->foreignId('orders.customer_id', constrainedOn: 'customers.id');
     * ```
     *
     * @param string $local The local column in the format `[this_table.]foreign_id`.
     * @param string $constrainedOn The foreign table in the format `other_table[.id]`.
     */
    public function foreignId(string $local, string $constrainedOn, OnDelete $onDelete = OnDelete::RESTRICT, OnUpdate $onUpdate = OnUpdate::NO_ACTION, bool $nullable = false): self
    {
        if (! str_contains($local, '.')) {
            $local = $this->tableName . '.' . $local;
        }

        if (! str_contains($constrainedOn, '.')) {
            $constrainedOn .= '.id';
        }

        return $this->belongsTo($local, $constrainedOn, $onDelete, $onUpdate, $nullable);
    }

    /**
     * Adds a foreign key constraint to another table.
     *
     * **Example**
     * ```php
     * new CreateTableStatement('orders')
     *     ->integer('customer_id', nullable: false)
     *     ->foreignKey('orders.customer_id', 'customers.id');
     * ```
     *
     * @param string $local The local column in the format `this_table.foreign_id`.
     * @param string $foreign The foreign column in the format `other_table.id`.
     */
    public function foreignKey(string $local, string $foreign, OnDelete $onDelete = OnDelete::RESTRICT, OnUpdate $onUpdate = OnUpdate::NO_ACTION): self
    {
        $this->statements[] = new BelongsToStatement(
            local: $local,
            foreign: $foreign,
            onDelete: $onDelete,
            onUpdate: $onUpdate,
        );

        return $this;
    }

    /**
     * Adds a `TEXT` column to the table.
     */
    public function text(string $name, bool $nullable = false, ?string $default = null): self
    {
        $this->statements[] = new TextStatement(
            name: $name,
            nullable: $nullable,
            default: $default,
        );

        return $this;
    }

    /**
     * Adds a `VARCHAR` column to the table.
     */
    public function varchar(string $name, int $length = 255, bool $nullable = false, ?string $default = null): self
    {
        $this->statements[] = new VarcharStatement(
            name: $name,
            size: $length,
            nullable: $nullable,
            default: $default,
        );

        return $this;
    }

    /**
     * Adds a `VARCHAR` column to the table.
     */
    public function string(string $name, int $length = 255, bool $nullable = false, ?string $default = null): self
    {
        return $this->varchar(
            name: $name,
            length: $length,
            nullable: $nullable,
            default: $default,
        );
    }

    /**
     * Adds a `CHAR` column to the table.
     */
    public function char(string $name, bool $nullable = false, ?string $default = null): self
    {
        $this->statements[] = new CharStatement(
            name: $name,
            nullable: $nullable,
            default: $default,
        );

        return $this;
    }

    /**
     * Adds an `INTEGER` column to the table.
     */
    public function integer(string $name, bool $unsigned = false, bool $nullable = false, ?int $default = null): self
    {
        $this->statements[] = new IntegerStatement(
            name: $name,
            unsigned: $unsigned,
            nullable: $nullable,
            default: $default,
        );

        return $this;
    }

    /**
     * Adds a `FLOAT` column to the table.
     */
    public function float(string $name, bool $nullable = false, ?float $default = null): self
    {
        $this->statements[] = new FloatStatement(
            name: $name,
            nullable: $nullable,
            default: $default,
        );

        return $this;
    }

    /**
     * Adds a datetime column to the table. Uses `DATETIME` for MySQL/SQLite and `TIMESTAMP` for PostgreSQL.
     */
    public function datetime(string $name, bool $nullable = false, ?string $default = null): self
    {
        $this->statements[] = new DatetimeStatement(
            name: $name,
            nullable: $nullable,
            default: $default,
        );

        return $this;
    }

    /**
     * Adds a `DATE` column to the table.
     */
    public function date(string $name, bool $nullable = false, ?string $default = null): self
    {
        $this->statements[] = new DateStatement(
            name: $name,
            nullable: $nullable,
            default: $default,
        );

        return $this;
    }

    /**
     * Adds a `BOOLEAN` column to the table.
     */
    public function boolean(string $name, bool $nullable = false, ?bool $default = null): self
    {
        $this->statements[] = new BooleanStatement(
            name: $name,
            nullable: $nullable,
            default: $default,
        );

        return $this;
    }

    /**
     * Adds a JSON column to the table. Uses `JSON` for MySQL, `TEXT` for SQLite, and `JSONB` for PostgreSQL.
     */
    public function json(string $name, bool $nullable = false, ?string $default = null): self
    {
        $this->statements[] = new JsonStatement(
            name: $name,
            nullable: $nullable,
            default: $default,
        );

        return $this;
    }

    /**
     * Adds a JSON column for storing serializable objects. This is an alias to the `json()` method.
     */
    public function dto(string $name, bool $nullable = false, ?string $default = null): self
    {
        $this->statements[] = new JsonStatement(
            name: $name,
            nullable: $nullable,
            default: $default,
        );

        return $this;
    }

    /**
     * Adds a JSON column for storing serializable objects. This is an alias to the `json()` method.
     */
    public function object(string $name, bool $nullable = false, ?string $default = null): self
    {
        $this->statements[] = new JsonStatement(
            name: $name,
            nullable: $nullable,
            default: $default,
        );

        return $this;
    }

    /**
     * Adds a JSON column for storing arrays. The default value is automatically JSON-encoded, as opposed to `json` and `object`.
     */
    public function array(string $name, bool $nullable = false, array $default = []): self
    {
        $this->statements[] = new JsonStatement(
            name: $name,
            nullable: $nullable,
            default: Json\encode($default),
        );

        return $this;
    }

    /**
     * Adds an enum column to the table. Uses the `ENUM` type for MySQL, falls back to `TEXT` for SQLite, and uses a custom enum type for PostgreSQL.
     */
    public function enum(string $name, string $enumClass, bool $nullable = false, null|UnitEnum|BackedEnum $default = null): self
    {
        $this->statements[] = new EnumStatement(
            name: $name,
            enumClass: $enumClass,
            nullable: $nullable,
            default: $default,
        );

        return $this;
    }

    /**
     * Adds a `SET` column to the table. Only supported in MySQL.
     */
    public function set(string $name, array $values, bool $nullable = false, ?string $default = null): self
    {
        $this->statements[] = new SetStatement(
            name: $name,
            values: $values,
            nullable: $nullable,
            default: $default,
        );

        return $this;
    }

    /**
     * Adds a unique constraint on the specified columns.
     */
    public function unique(string ...$columns): self
    {
        $this->trailingStatements[] = new UniqueStatement(
            tableName: $this->tableName,
            columns: $columns,
        );

        return $this;
    }

    /**
     * Adds an index on the specified columns.
     */
    public function index(string ...$columns): self
    {
        $this->trailingStatements[] = new IndexStatement(
            tableName: $this->tableName,
            columns: $columns,
        );

        return $this;
    }

    /**
     * Adds a raw SQL statement to the table definition.
     */
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

        return $createTable;
    }
}
