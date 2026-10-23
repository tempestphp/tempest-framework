<?php

namespace Tempest\Database\Columns;

use InvalidArgumentException;
use Tempest\Database\Columns\Concerns\HasConstraints;
use Tempest\Database\Columns\Concerns\IsColumn;

final class DateTimeColumn implements Column
{
    use IsColumn;

    private int $precision = 0;
    private bool $current = false;
    private bool $currentOnUpdate = false;

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

    public function getPrecision(): int
    {
        return $this->precision;
    }

    public function current(): self
    {
        return clone($this, [
            'current' => true,
        ]);
    }

    public function usesCurrent(): bool
    {
        return $this->current;
    }

    public function currentOnUpdate(): self
    {
        return clone($this, [
            'currentOnUpdate' => true,
        ]);
    }

    public function usesCurrentOnUpdate(): bool
    {
        return $this->currentOnUpdate;
    }
}
