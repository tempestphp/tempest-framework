<?php

namespace Tempest\Support\Memoization;

use Closure;

trait HasMemoization
{
    private array $memoize = [];

    private function memoize(string $key, Closure $closure): mixed
    {
        if (! array_key_exists($key, $this->memoize)) {
            $this->memoize[$key] = $closure();
        }

        return $this->memoize[$key];
    }
}
