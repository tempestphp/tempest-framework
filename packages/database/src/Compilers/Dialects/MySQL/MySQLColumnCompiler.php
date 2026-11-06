<?php

namespace Tempest\Database\Compilers\Dialects\MySQL;

use InvalidArgumentException;
use Tempest\Database\Columns\CharColumn;
use Tempest\Database\Columns\Column;
use Tempest\Database\Columns\DateColumn;
use Tempest\Database\Columns\DateTimeColumn;
use Tempest\Database\Columns\FloatColumn;
use Tempest\Database\Columns\IntegerColumn;
use Tempest\Database\Columns\TextColumn;
use Tempest\Database\Columns\UuidColumn;
use Tempest\Database\Columns\VarcharColumn;
use Tempest\Database\Compilers\ColumnCompiler;

final class MySQLColumnCompiler implements ColumnCompiler
{
    public function compile(Column $column): string
    {
        $parts = [
            $this->quote($column->name),
            $this->compileType($column),
            $this->compileNullability($column),
            $this->compileDefault($column),
            $this->compileModifiers($column),
        ];

        return implode(' ', array_filter($parts));
    }

    private function compileType(Column $column): string
    {
        return match (true) {
            $column instanceof CharColumn => "CHAR({$column->length})",
            $column instanceof VarcharColumn => "VARCHAR({$column->length})",
            $column instanceof TextColumn => $this->compileTextType($column),
            $column instanceof IntegerColumn => 'INT' . ($column->unsigned ? ' UNSIGNED' : ''),
            $column instanceof FloatColumn => 'FLOAT',
            $column instanceof DateColumn => 'DATE',
            $column instanceof DateTimeColumn => "DATETIME({$column->precision})",
            $column instanceof UuidColumn => 'CHAR(36)',
            default => throw new \LogicException(
                'Unsupported column: ' . $column::class,
            ),
        };
    }

    private function compileTextType(TextColumn $column): string
    {
        return match (true) {
            $column->length <= 255 => 'TINYTEXT',
            $column->length <= 65_535 => 'TEXT',
            $column->length <= 16_777_215 => 'MEDIUMTEXT',
            $column->length <= 4_294_967_295 => 'LONGTEXT',
            default => new \LogicException('Unsupported text length'),
        };
    }

    private function compileNullability(Column $column): string
    {
        return $column->nullable ? 'NULL' : 'NOT NULL';
    }

    private function compileDefault(Column $column): string
    {
        if ($column instanceof DateColumn && $column->current) {
            return 'DEFAULT (CURRENT_DATE)';
        }

        if ($column instanceof DateTimeColumn && $column->current) {
            $precision = $column->precision;

            return "DEFAULT CURRENT_TIMESTAMP($precision)";
        }

        if ($column instanceof UuidColumn && $column->generated) {
            return 'DEFAULT (UUID())';
        }

        if (! isset($column->default)) {
            return '';
        }

        $value = $this->literal($column->default);

        if ($column instanceof TextColumn) {
            return "DEFAULT ($value)";
        }

        return "DEFAULT $value";
    }

    private function compileModifiers(Column $column): string
    {
        $parts = [];

        if ($column instanceof IntegerColumn && $column->autoIncrement) {
            $parts[] = 'AUTO_INCREMENT';
        }

        if ($column instanceof DateTimeColumn && $column->currentOnUpdate) {
            $precision = $column->precision;

            $parts[] = "ON UPDATE CURRENT_TIMESTAMP($precision)";
        }

        if ($column->unique) {
            $parts[] = 'UNIQUE';
        }

        if ($column->primaryKey) {
            $parts[] = 'PRIMARY KEY';
        }

        return implode(' ', $parts);
    }

    private function quote(string $value): string
    {
        return '`' . str_replace('`', '``', $value) . '`';
    }

    private function literal(mixed $value): string
    {
        return match (true) {
            $value === null => 'NULL',
            is_bool($value) => $value ? '1' : '0',
            is_int($value), is_float($value) => (string) $value,
            is_string($value) => "'" . str_replace("'", "''", $value) . "'",
            default => throw new InvalidArgumentException(
                'Unsupported default value.',
            ),
        };
    }
}
