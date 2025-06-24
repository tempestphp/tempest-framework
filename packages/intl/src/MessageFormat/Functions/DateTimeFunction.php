<?php

namespace Tempest\Intl\MessageFormat\Functions;

use Tempest\DateTime\DateTime;
use Tempest\Intl\MessageFormat\Formatter\FormattedValue;
use Tempest\Intl\MessageFormat\FormattingFunction;
use Tempest\Support\Arr;

final class DateTimeFunction implements FormattingFunction
{
    public string $name = 'datetime';

    public function format(mixed $value, array $parameters): FormattedValue
    {
        if (! class_exists(DateTime::class)) {
            throw new \RuntimeException('`tempest/datetime` is required to use the `datetime` function.');
        }

        $datetime = DateTime::parse($value);
        $formatted = $datetime->format(Arr\get_by_key($parameters, 'pattern'));

        return new FormattedValue($value, $formatted);
    }
}
