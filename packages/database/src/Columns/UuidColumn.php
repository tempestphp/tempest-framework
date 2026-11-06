<?php

namespace Tempest\Database\Columns;

use Tempest\Database\Columns\Concerns\IsColumn;

final class UuidColumn implements Column
{
    use IsColumn;

    private(set) bool $generated = false;

    public function generated(): self
    {
        return clone($this, [
            'generated' => true,
        ]);
    }
}
