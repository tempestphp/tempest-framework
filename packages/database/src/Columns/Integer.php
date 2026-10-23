<?php

namespace Tempest\Database\Columns;

final class Integer implements Column
{
    use IsColumn;
    use HasConstraints;

    private bool $autoIncrement = false;

    private bool $unsigned = false;

    public function autoIncrement(bool $autoIncrement = true): self
    {
        $column = clone $this;

        $column->autoIncrement = $autoIncrement;

        return $column;
    }

    public function shouldAutoIncrement(): bool
    {
        return $this->autoIncrement;
    }

    public function unsigned(bool $unsigned = true): self
    {
        $column = clone $this;

        $column->unsigned = $unsigned;

        return $column;
    }

    public function isUnsigned(): bool
    {
        return $this->unsigned;
    }
}
