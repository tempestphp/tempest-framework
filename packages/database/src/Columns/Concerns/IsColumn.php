<?php

namespace Tempest\Database\Columns\Concerns;

trait IsColumn
{
    private bool $primaryKey = false;

    private bool $nullable = false;

    private mixed $default;

    private bool $unique = false;

    public function __construct(
        private string $name,
    ) {}

    public function name(string $name): static
    {
        return clone($this, [
            'name' => $name,
        ]);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function primaryKey(): static
    {
        return clone($this, [
            'primaryKey' => true,
        ]);
    }

    public function isPrimaryKey(): bool
    {
        return $this->primaryKey;
    }

    public function nullable(bool $nullable = true): static
    {
        return clone($this, [
            'nullable' => $nullable,
        ]);
    }

    public function isNullable(): bool
    {
        return $this->nullable;
    }

    public function default(mixed $default): static
    {
        return clone($this, [
            'default' => $default,
        ]);
    }

    public function hasDefault(): bool
    {
        return isset($this->default);
    }

    public function getDefault(): mixed
    {
        return $this->default;
    }

    public function unique(bool $unique = true): self
    {
        return clone($this, [
            'unique' => $unique,
        ]);
    }

    public function isUnique(): bool
    {
        return $this->unique;
    }
}
