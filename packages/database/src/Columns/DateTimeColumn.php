<?php

namespace Tempest\Database\Columns;

use InvalidArgumentException;
use Tempest\Database\Columns\Concerns\IsColumn;

final class DateTimeColumn implements Column
{
    use IsColumn;

    private(set) int $precision = 0;
    private(set) bool $current = false;
    private(set) bool $currentOnUpdate = false;

    public function precision(int $precision): self
    {
        if ($precision < 0 || $precision > 6) {
            throw new InvalidArgumentException(
                'Precision must be between 0 and 6.',
            );
        }

        return clone($this, [
            'precision' => $precision,
        ]);
    }

    public function current(): self
    {
        return clone($this, [
            'current' => true,
        ]);
    }

    public function currentOnUpdate(): self
    {
        return clone($this, [
            'currentOnUpdate' => true,
        ]);
    }
}
