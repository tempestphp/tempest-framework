<?php

namespace Tempest\Reflection;

use Closure;

trait HasMemoization
{
    private array $memoize = [];

    private function memoize(string $key, Closure $closure): mixed
    {
        $this->memoize[$key] ??= $closure();

        return $this->memoize[$key];
    }
}