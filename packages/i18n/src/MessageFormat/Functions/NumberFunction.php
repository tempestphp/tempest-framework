<?php

namespace Tempest\Internationalization\MessageFormat\Functions;

use Tempest\Internationalization\MessageFormat\Formatter\FormattedValue;
use Tempest\Internationalization\MessageFormat\Formatter\MessageFormatFunction;
use Tempest\Support\Arr;
use Tempest\Support\Currency;
use Tempest\Support\Number;

final class NumberFunction implements MessageFormatFunction
{
    public string $name = 'number';

    public function evaluate(mixed $value, array $parameters): FormattedValue
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
