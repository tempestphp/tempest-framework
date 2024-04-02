<?php

declare(strict_types=1);

namespace Tempest\Support;

class BaseBuilder
{
    public function when(bool $condition, callable $callback): static
    {
        if ($condition) {
            $callback($this);
        }

        return $this;
    }

    public function unless(bool $condition, callable $callback): static
    {
        return $this->when(! $condition, $callback);
    }
}
