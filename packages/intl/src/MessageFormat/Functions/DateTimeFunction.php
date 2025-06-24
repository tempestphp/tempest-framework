<?php

namespace Tempest\Intl\MessageFormat\Functions;

use DateTime;
use Tempest\DateTime\DateTime as TempestDateTime;
use Tempest\Intl\MessageFormat\Formatter\FormattedValue;
use Tempest\Intl\MessageFormat\FormattingFunction;
use Tempest\Support\Arr;

final class DateTimeFunction implements FormattingFunction
{
    public string $name = 'datetime';

    public function format(mixed $value, array $parameters): FormattedValue
    {
        if (class_exists(TempestDateTime::class)) {
            $datetime = TempestDateTime::parse($value);
        } else {
            $datetime = new DateTime($value);
        }

        $formatted = $datetime->format(Arr\get_by_key($parameters, 'pattern'));

        return new FormattedValue($value, $formatted);
    }
}
