<?php

namespace Tempest\Database\Columns;

use Tempest\Database\Columns\Concerns\HasConstraints;
use Tempest\Database\Columns\Concerns\IsColumn;

final class FloatColumn implements Column
{
    use IsColumn;

    private bool $unsigned = false;

    public function unsigned(): self
    {
        return clone($this, [
            'unsigned' => true,
        ]);
    }

    public function isUnsigned(): bool
    {
        return $this->unsigned;
    }
}
