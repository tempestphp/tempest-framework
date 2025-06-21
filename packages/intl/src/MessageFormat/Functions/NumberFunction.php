<?php

namespace Tempest\Intl\MessageFormat\Functions;

use Tempest\Intl\MessageFormat\Formatter\FormattedValue;
use Tempest\Intl\MessageFormat\FormattingFunction;
use Tempest\Intl\Number;
use Tempest\Support\Arr;
use Tempest\Support\Currency;

final class NumberFunction implements FormattingFunction
{
    public string $name = 'number';

    public function format(mixed $value, array $parameters): FormattedValue
    {
        $number = Number\parse($value);
        $formatted = match (Arr\get_by_key($parameters, 'style')) {
            'percent' => Number\to_percentage($number),
            'currency' => Number\currency($number, Currency::parse(Arr\get_by_key($parameters, 'currency'))),
            default => Number\format($number),
        };

        return new FormattedValue($number, $formatted);
    }
}
