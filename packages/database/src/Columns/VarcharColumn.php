<?php

namespace Tempest\Database\Columns;

use Tempest\Database\Columns\Concerns\IsColumn;

final class VarcharColumn implements Column
{
    use IsColumn;

    private(set) int $length = 255;

    public function length(int $length): self
    {
        return clone($this, [
            'length' => $length,
        ]);
    }
}
