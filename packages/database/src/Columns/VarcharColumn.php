<?php

namespace Tempest\Database\Columns;

use Tempest\Database\Columns\Concerns\HasConstraints;
use Tempest\Database\Columns\Concerns\IsColumn;

final class VarcharColumn implements Column
{
    use IsColumn;

    private int $length = 255;

    public function length(int $length): self
    {
        return clone($this, [
            'length' => $length,
        ]);
    }

    public function getLength(): int
    {
        return $this->length;
    }
}
