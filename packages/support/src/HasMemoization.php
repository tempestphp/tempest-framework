<?php

namespace Tempest\Support;

use Closure;

trait HasMemoization
{
    private array $memoize = [];

    private function memoize(string $key, Closure $closure): mixed
    {
        if (! isset($this->memoize[$key])) {
            $this->memoize[$key] = $closure();
        }

        return $this->memoize[$key];
    }
}
