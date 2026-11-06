<?php

namespace Tempest\Database\Columns;

use Tempest\Database\Columns\Concerns\IsColumn;

final class IntegerColumn implements Column
{
    use IsColumn;

    private(set) bool $autoIncrement = false;

    private(set) bool $unsigned = false;

    public function autoIncrement(bool $autoIncrement = true): self
    {
        return clone($this, [
            'autoIncrement' => $autoIncrement,
        ]);
    }

    public function unsigned(bool $unsigned = true): self
    {
        return clone($this, [
            'unsigned' => $unsigned,
        ]);
    }
}
