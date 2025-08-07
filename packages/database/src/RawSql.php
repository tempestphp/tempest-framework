<?php

namespace Tempest\Database;

use Tempest\Database\Config\DatabaseDialect;
use Tempest\Support\Str\ImmutableString;

final class RawSql
{
    public function __construct(
        private(set) DatabaseDialect $dialect,
        private(set) string $sql,
        private(set) array $bindings,
    ) {}

    public function compile(): string
    {
        $resolvedBindings = $this->resolveBindingsForDisplay();

        if (! array_is_list($resolvedBindings)) {
            return $this->replaceNamedBindings($this->sql, $resolvedBindings);
        }

        return $this->replacePositionalBindings($this->sql, array_values($resolvedBindings));
    }

    public function toImmutableString(): ImmutableString
    {
        return new ImmutableString($this->compile());
    }

    public function __toString(): string
    {
        return $this->compile();
    }

    private function replaceNamedBindings(string $sql, array $bindings): string
    {
        foreach ($bindings as $key => $value) {
            $placeholder = ':' . $key;
            $formattedValue = $this->formatValueForSql($value);
            $sql = str_replace($placeholder, $formattedValue, $sql);
        }

        return $sql;
    }

    private function replacePositionalBindings(string $sql, array $bindings): string
    {
        $bindingIndex = 0;
        $result = '';
        $length = strlen($sql);

        for ($i = 0; $i < $length; $i++) {
            if ($sql[$i] === '?' && $bindingIndex < count($bindings)) {
                $value = $bindings[$bindingIndex];
                $result .= $this->formatValueForSql($value);
                $bindingIndex++;
            } else {
                $result .= $sql[$i];
            }
        }

        return $result;
    }

    private function resolveBindingsForDisplay(): array
    {
        $bindings = [];

        foreach ($this->bindings as $key => $value) {
            if (is_bool($value)) {
                $value = match ($this->dialect) {
                    DatabaseDialect::POSTGRESQL => $value ? 'true' : 'false',
                    default => $value ? '1' : '0',
                };
            }

            if ($value instanceof Query) {
                $value = '(' . $value->toRawSql() . ')';
            }

            $bindings[$key] = $value;
        }

        return $bindings;
    }

    private function formatValueForSql(mixed $value): string
    {
        if ($value === null) {
            return 'NULL';
        }

        if (is_string($value)) {
            if (str_starts_with($value, '(') && str_ends_with($value, ')')) {
                return $value;
            }

            return "'" . str_replace("'", "''", $value) . "'";
        }

        if (is_numeric($value)) {
            return (string) $value;
        }

        return "'" . str_replace("'", "''", (string) $value) . "'";
    }
}
