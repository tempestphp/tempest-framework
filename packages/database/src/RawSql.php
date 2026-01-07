<?php

namespace Tempest\Database;

use Tempest\Database\Config\DatabaseDialect;
use Tempest\Mapper\SerializerFactory;
use Tempest\Support\Str\ImmutableString;

final class RawSql
{
    private ?RawSqlDatabaseContext $context {
        get => $this->context ??= new RawSqlDatabaseContext($this->dialect);
    }

    public function __construct(
        private(set) DatabaseDialect $dialect,
        private(set) string $sql,
        private(set) array $bindings,
        private SerializerFactory $serializerFactory,
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
            $sql = str_replace(
                search: ':' . $key,
                replace: $this->formatValueForSql($value),
                subject: $sql,
            );
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
            if ($value instanceof Query) {
                $bindings[$key] = "({$value->toRawSql()})";
                continue;
            }

            if ($serializer = $this->serializerFactory->in($this->context)->forValue($value)) {
                $bindings[$key] = $serializer->serialize($value);
                continue;
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

        return (string) $value;
    }
}
