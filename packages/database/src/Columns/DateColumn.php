<?php

namespace Tempest\Database\Columns;

use Tempest\Database\Columns\Concerns\HasConstraints;
use Tempest\Database\Columns\Concerns\IsColumn;

final class DateColumn implements Column
{
    use IsColumn;

    private bool $current = false;

    public function useCurrent(): self
    {
        return clone($this, [
            'current' => true,
        ]);
    }

    public function usesCurrent(): bool
    {
        return $this->current;
    }
}
