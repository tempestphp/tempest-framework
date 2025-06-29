<?php

namespace Tempest\Intl\MessageFormat\Functions;

use Tempest\Intl\MessageFormat\SelectorFunction;

final class BooleanFunction implements SelectorFunction
{
    public string $name = 'boolean';

    public function match(string $key, mixed $value, array $parameters): bool
    {
        return match ((bool) $value) {
            true => in_array($key, ['true', true], strict: true),
            false => in_array($key, ['false', false], strict: true),
        };
    }
}
