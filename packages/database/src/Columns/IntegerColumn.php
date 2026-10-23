<?php

namespace Tempest\Database\Columns;

use Tempest\Database\Columns\Concerns\HasConstraints;
use Tempest\Database\Columns\Concerns\IsColumn;

final class IntegerColumn implements Column
{
    use IsColumn;

    private bool $autoIncrement = false;

    private bool $unsigned = false;

    public function autoIncrement(bool $autoIncrement = true): self
    {
        return clone($this, [
            'autoIncrement' => $autoIncrement,
        ]);
    }

    public function shouldAutoIncrement(): bool
    {
        return $this->autoIncrement;
    }

    public function unsigned(bool $unsigned = true): self
    {
        return clone($this, [
            'unsigned' => $unsigned,
        ]);
    }

    public function isUnsigned(): bool
    {
        return $this->unsigned;
    }
}
