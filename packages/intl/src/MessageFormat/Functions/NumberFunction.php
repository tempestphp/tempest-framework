<?php

namespace Tempest\Intl\MessageFormat\Functions;

use Tempest\Intl\Currency;
use Tempest\Intl\IntlConfig;
use Tempest\Intl\Locale;
use Tempest\Intl\MessageFormat\Formatter\FormattedValue;
use Tempest\Intl\MessageFormat\FormattingFunction;
use Tempest\Intl\MessageFormat\SelectorFunction;
use Tempest\Intl\Number;
use Tempest\Intl\PluralRules\PluralRulesMatcher;
use Tempest\Support\Arr;
use Tempest\Support\Str;

final class NumberFunction implements FormattingFunction, SelectorFunction
{
    public string $name = 'number';

    public function __construct(
        private readonly IntlConfig $intlConfig,
        private readonly PluralRulesMatcher $pluralRules = new PluralRulesMatcher(),
    ) {}

    public function match(string $key, mixed $value, array $parameters): bool
    {
        $number = Number\parse($value);

        if (Arr\get_by_key($parameters, 'select') === 'exact') {
            return $key === Str\parse($value);
        }

        if (Number\parse($key) === $number || $key === $value) {
            return true;
        }

        if ($key === $this->pluralRules->getPluralCategory($this->intlConfig->currentLocale, $number)) {
            return true;
        }

        return false;
    }

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
