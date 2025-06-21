<?php

namespace Tempest\Intl\MessageFormat\Functions;

use Tempest\DateTime\DateTime;
use Tempest\Intl\MessageFormat\Formatter\FormattedValue;
use Tempest\Intl\MessageFormat\Formatter\MessageFormatFunction;
use Tempest\Support\Arr;

final class DateTimeFunction implements MessageFormatFunction
{
    public string $name = 'datetime';

    public function evaluate(mixed $value, array $parameters): FormattedValue
    {
        $datetime = DateTime::parse($value);
        $formatted = $datetime->format(Arr\get_by_key($parameters, 'pattern'));

        return new FormattedValue($value, $formatted);
    }
}
