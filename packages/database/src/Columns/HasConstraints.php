<?php

namespace Tempest\Database\Columns;

trait HasConstraints
{
    private bool $primary = false;

    private bool $unique = false;

    public function primary(bool $primary = true): self
    {
        $column = clone $this;

        $column->primary = $primary;

        return $column;
    }

    public function isPrimary(): bool
    {
        return $this->primary;
    }

    public function unique(bool $unique = true): self
    {
        $column = clone $this;

        $column->unique = $unique;

        return $column;
    }

    public function isUnique(): bool
    {
        return $this->unique;
    }
}
