<?php

namespace Tempest\Intl\MessageFormat\Functions;

use Tempest\DateTime\DateStyle;
use Tempest\DateTime\DateTime;
use Tempest\DateTime\TimeStyle;
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

        if ($pattern = Arr\get_by_key($parameters, 'pattern')) {
            return new FormattedValue($value, $datetime->format($pattern));
        }

        return new FormattedValue(
            value: $value,
            formatted: $datetime->toString(
                dateStyle: match (Arr\get_by_key($parameters, 'date_style')) {
                    'full' => DateStyle::FULL,
                    'long' => DateStyle::LONG,
                    'medium' => DateStyle::MEDIUM,
                    'short' => DateStyle::SHORT,
                    'none' => DateStyle::NONE,
                    'relative' => DateStyle::RELATIVE_MEDIUM,
                    default => DateStyle::LONG,
                },
                timeStyle: match (Arr\get_by_key($parameters, 'time_style')) {
                    'full' => TimeStyle::FULL,
                    'long' => TimeStyle::LONG,
                    'medium' => TimeStyle::MEDIUM,
                    'short' => TimeStyle::SHORT,
                    'none' => TimeStyle::NONE,
                    default => TimeStyle::SHORT,
                },
            ),
        );
    }
}
