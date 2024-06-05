<?php

declare(strict_types=1);

namespace Tempest\Support\Conditions;

trait HasConditions
{
    /**
     * @param bool $condition
     * @param callable $callback
     *
     * @return $this
     */
    public function when(bool $condition, callable $callback): self
    {
        if ($condition) {
            $callback($this);
        }

        return $this;
    }

    public function unless(bool $condition, callable $callback): self
    {
        return $this->when(! $condition, $callback);
    }
}
