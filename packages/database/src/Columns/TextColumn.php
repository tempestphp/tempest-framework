<?php

namespace Tempest\Database\Columns;

use Tempest\Database\Columns\Concerns\IsColumn;

final class TextColumn implements Column
{
    use IsColumn;

    private(set) int $length = 65_535;

    public function length(int $length): self
    {
        return clone($this, [
            'length' => $length,
        ]);
    }
}
