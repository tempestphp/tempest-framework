<?php

declare(strict_types=1);

namespace Tempest\Support\Conditions;

use Closure;

trait HasConditions
{
    /**
     * Applies the given `$callback` if the `$condition` is true.
     *
     * @param mixed|Closure(static): bool $condition
     * @param Closure(static): self $callback
     */
    public function when(mixed $condition, Closure $callback): self
    {
        if ($condition instanceof Closure) {
            $condition = $condition($this);
        }

        if ($condition) {
            $callback($this);
        }

        return $this;
    }

    /**
     * Applies the given `$callback` if the `$condition` is false.
     *
     * @param mixed|Closure(static): bool $condition
     * @param Closure(static): self $callback
     */
    public function unless(mixed $condition, Closure $callback): self
    {
        if ($condition instanceof Closure) {
            $condition = $condition($this);
        }

        return $this->when(! $condition, $callback);
    }
}
