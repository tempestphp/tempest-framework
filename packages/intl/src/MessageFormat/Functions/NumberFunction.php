<?php

namespace Tempest\Intl\MessageFormat\Functions;

use Tempest\Intl\Currency;
use Tempest\Intl\IntlConfig;
use Tempest\Intl\MessageFormat\Formatter\FormattedValue;
use Tempest\Intl\MessageFormat\FormattingFunction;
use Tempest\Intl\MessageFormat\SelectorFunction;
use Tempest\Intl\Number;
use Tempest\Intl\PluralRules\PluralRulesMatcher;
use Tempest\Support\Arr;

final class NumberFunction implements FormattingFunction, SelectorFunction
{
    /** @var array<string, string> */
    private array $ordinalCategories = [];

    public string $name = 'number';

    public function __construct(
        private readonly IntlConfig $intlConfig,
        private readonly PluralRulesMatcher $pluralRules = new PluralRulesMatcher(),
    ) {}

    public function match(string $key, mixed $value, array $parameters): bool
    {
        $number = Number\parse($value);

        if (Arr\get_by_key($parameters, 'select') === 'exists') {
            return $this->matchExists($key, $value);
        }

        if (Arr\get_by_key($parameters, 'select') === 'exact') {
            return Number\parse($key) === $value;
        }

        if (Number\parse($key) === $number || $key === $value) {
            return true;
        }

        if (Arr\get_by_key($parameters, 'select') === 'ordinal') {
            return $key === $this->getOrdinalCategory($number);
        }

        return $key === $this->pluralRules->getPluralCategory($this->intlConfig->currentLocale, $number);
    }

    private function getOrdinalCategory(float|int $number): string
    {
        $cacheKey = "{$this->intlConfig->currentLocale->value}:{$number}";

        return $this->ordinalCategories[$cacheKey] ??= \MessageFormatter::formatMessage(
            $this->intlConfig->currentLocale->value,
            '{number, selectordinal, zero {zero} one {one} two {two} few {few} many {many} other {other}}',
            ['number' => $number],
        ) ?: 'other';
    }

    private function matchExists(string $key, mixed $value): bool
    {
        if ($key === 'true') {
            return $value !== null;
        }

        if ($key === 'false' || $key === 'null') {
            return $value === null;
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
