<?php

namespace Tempest\Database\Columns\Concerns;

trait IsColumn
{
    private(set) bool $primaryKey = false;

    private(set) bool $nullable = false;

    private(set) mixed $default;

    private(set) bool $unique = false;

    public function __construct(
        private(set) string $name,
    ) {}

    public function name(string $name): static
    {
        return clone($this, [
            'name' => $name,
        ]);
    }

    public function primaryKey(): static
    {
        return clone($this, [
            'primaryKey' => true,
        ]);
    }

    public function nullable(bool $nullable = true): static
    {
        return clone($this, [
            'nullable' => $nullable,
        ]);
    }

    public function default(mixed $default): static
    {
        return clone($this, [
            'default' => $default,
        ]);
    }

    public function unique(bool $unique = true): self
    {
        return clone($this, [
            'unique' => $unique,
        ]);
    }
}
