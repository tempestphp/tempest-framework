<?php

namespace Tempest\Database\Columns;

trait IsColumn
{
    private bool $nullable = false;

    private mixed $default;

    public function __construct(
        private string $name,
    ) {}

    public function name(string $name): static
    {
        $column = clone $this;

        $column->name = $name;

        return $column;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function nullable(bool $nullable = true): static
    {
        $column = clone $this;

        $column->nullable = $nullable;

        return $column;
    }

    public function isNullable(): bool
    {
        return $this->nullable;
    }

    public function default(mixed $default): static
    {
        $column = clone $this;

        $column->default = $default;

        return $column;
    }

    public function hasDefault(): bool
    {
        return isset($this->default);
    }

    public function getDefault(): mixed
    {
        return $this->default;
    }
}
