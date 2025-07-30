<?php

namespace Tempest\Intl\MessageFormat\Functions;

use Tempest\Intl\MessageFormat\Formatter\FormattedValue;
use Tempest\Intl\MessageFormat\FormattingFunction;
use Tempest\Intl\MessageFormat\SelectorFunction;

final class BooleanFunction implements SelectorFunction, FormattingFunction
{
    public string $name = 'boolean';

    public function match(string $key, mixed $value, array $parameters): bool
    {
        return match ($value) {
            [], null, false, 'false' => in_array($key, ['false', false, null, 'no', 0, '0'], strict: true),
            default => in_array($key, ['true', true, 'yes', 1, '1'], strict: true),
        };
    }

    public function format(mixed $value, array $parameters): FormattedValue
    {
        return new FormattedValue($value, match ($value) {
            [], null, false, 'false' => 'false',
            default => 'true',
        });
    }
}
