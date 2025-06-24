<?php

namespace Tempest\Intl\MessageFormat;

interface SelectorFunction
{
    /**
     * Identifier of the selector function.
     */
    public string $name {
        get;
    }

    /**
     * Defines whether the matcher key matches with the given value.
     */
    public function match(string $key, mixed $value, array $parameters): bool;
}
