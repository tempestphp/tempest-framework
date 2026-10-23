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
            $this->quote($column->getName()),
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
            $column instanceof CharColumn => "CHAR({$column->getLength()})",
            $column instanceof VarcharColumn => "VARCHAR({$column->getLength()})",
            $column instanceof TextColumn => $this->compileTextType($column),
            $column instanceof IntegerColumn => 'INT' . ($column->isUnsigned() ? ' UNSIGNED' : ''),
            $column instanceof FloatColumn => 'FLOAT',
            $column instanceof DateColumn => 'DATE',
            $column instanceof DateTimeColumn => "DATETIME({$column->getPrecision()})",
            $column instanceof UuidColumn => 'CHAR(36)',
            default => throw new \LogicException(
                'Unsupported column: ' . $column::class,
            ),
        };
    }

    private function compileTextType(TextColumn $column): string
    {
        return match (true) {
            $column->getLength() <= 255 => 'TINYTEXT',
            $column->getLength() <= 65_535 => 'TEXT',
            $column->getLength() <= 16_777_215 => 'MEDIUMTEXT',
            $column->getLength() <= 4_294_967_295 => 'LONGTEXT',
            default => new \LogicException('Unsupported text length'),
        };
    }

    private function compileNullability(Column $column): string
    {
        return $column->isNullable() ? 'NULL' : 'NOT NULL';
    }

    private function compileDefault(Column $column): string
    {
        if ($column instanceof DateColumn && $column->usesCurrent()) {
            return 'DEFAULT (CURRENT_DATE)';
        }

        if ($column instanceof DateTimeColumn && $column->usesCurrent()) {
            $precision = $column->getPrecision();

            return "DEFAULT CURRENT_TIMESTAMP($precision)";
        }

        if ($column instanceof UuidColumn && $column->usesGenerated()) {
            return 'DEFAULT (UUID())';
        }

        if (! $column->hasDefault()) {
            return '';
        }

        $value = $this->literal($column->getDefault());

        if ($column instanceof TextColumn) {
            return "DEFAULT ($value)";
        }

        return "DEFAULT $value";
    }

    private function compileModifiers(Column $column): string
    {
        $parts = [];

        if ($column instanceof IntegerColumn && $column->shouldAutoIncrement()) {
            $parts[] = 'AUTO_INCREMENT';
        }

        if ($column instanceof DateTimeColumn && $column->usesCurrentOnUpdate()) {
            $precision = $column->getPrecision();

            $parts[] = "ON UPDATE CURRENT_TIMESTAMP($precision)";
        }

        if ($column->isUnique()) {
            $parts[] = 'UNIQUE';
        }

        if ($column->isPrimaryKey()) {
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
