<?php

namespace Tempest\Support\Number;

use NumberFormatter;
use Tempest\Support\Currency;
use Tempest\Support\Language\Locale;
use Tempest\Support\Math;

/**
 * Formats the given number.
 *
 * @see https://www.php.net/manual/en/class.numberformatter.php
 */
function format(int|float $number, ?int $precision = null, ?int $maxPrecision = null, ?Locale $locale = null): string|false
{
    $locale ??= Locale::ENGLISH;
    $formatter = new NumberFormatter($locale->value, NumberFormatter::DECIMAL);

    if (! is_null($maxPrecision)) {
        $formatter->setAttribute(NumberFormatter::MAX_FRACTION_DIGITS, $maxPrecision);
    } elseif (! is_null($precision)) {
        $formatter->setAttribute(NumberFormatter::FRACTION_DIGITS, $precision);
    }

    return $formatter->format($number);
}

/**
 * Spells out the given number in the given locale.
 *
 * @see https://www.php.net/manual/en/class.numberformatter.php
 */
function spell_out(int|float $number, ?Locale $locale = null, ?int $after = null, ?int $until = null): string|false
{
    $locale ??= Locale::ENGLISH;

    if (! is_null($after) && $number <= $after) {
        return namespace\format($number, locale: $locale);
    }

    if (! is_null($until) && $number >= $until) {
        return namespace\format($number, locale: $locale);
    }

    $formatter = new NumberFormatter($locale->value, NumberFormatter::SPELLOUT);

    return $formatter->format($number);
}

/**
 * Converts the given number to ordinal form.
 *
 * @see https://www.php.net/manual/en/class.numberformatter.php
 */
function to_ordinal(int|float $number, ?Locale $locale = null): string|false
{
    $locale ??= Locale::ENGLISH;
    $formatter = new NumberFormatter($locale->value, NumberFormatter::ORDINAL);

    return $formatter->format($number);
}

/**
 * Spells out the given number in the given locale in ordinal form.
 *
 * @see https://www.php.net/manual/en/class.numberformatter.php
 */
function to_spelled_ordinal(int|float $number, ?Locale $locale = null): string|false
{
    $locale ??= Locale::ENGLISH;
    $formatter = new NumberFormatter($locale->value, NumberFormatter::SPELLOUT);
    $formatter->setTextAttribute(NumberFormatter::DEFAULT_RULESET, '%spellout-ordinal');

    return $formatter->format($number);
}

/**
 * Converts the given number to its percentage equivalent.
 */
function to_percentage(int|float $number, int $precision = 0, ?int $maxPrecision = null, ?Locale $locale = null): string|false
{
    $locale ??= Locale::ENGLISH;
    $formatter = new NumberFormatter($locale->value, NumberFormatter::PERCENT);

    if (! is_null($maxPrecision)) {
        $formatter->setAttribute(NumberFormatter::MAX_FRACTION_DIGITS, $maxPrecision);
    } else {
        $formatter->setAttribute(NumberFormatter::FRACTION_DIGITS, $precision);
    }

    return $formatter->format($number / 100);
}

/**
 * Converts the given number to its currency equivalent.
 */
function currency(int|float $number, Currency $currency, ?Locale $locale = null, ?int $precision = null): string|false
{
    $locale ??= Locale::ENGLISH;
    $formatter = new NumberFormatter($locale->value, NumberFormatter::CURRENCY);

    if (! is_null($precision)) {
        $formatter->setAttribute(NumberFormatter::FRACTION_DIGITS, $precision);
    }

    return $formatter->formatCurrency($number, $currency->name);
}

/**
 * Converts the given number of bytes to its human-readable file size equivalent.
 */
function to_file_size(int|float $bytes, int $precision = 0, ?int $maxPrecision = null, bool $useBinaryPrefix = false): string
{
    $base = $useBinaryPrefix ? 1024 : 1000;
    $units = $useBinaryPrefix
        ? ['B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB', 'RiB', 'QiB']
        : ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB', 'RB', 'QB'];

    for ($i = 0; ($bytes / $base) > 0.9 && $i < (count($units) - 1); $i++) {
        $bytes /= $base;
    }

    return sprintf('%s %s', namespace\format($bytes, $precision, $maxPrecision), $units[$i]);
}

/**
 * Converts the number to its human-readable equivalent.
 */
function to_human_readable(int|float $number, int $precision = 0, ?int $maxPrecision = null, array $units = []): string|false
{
    if ($units === []) {
        $units = [
            3 => 'K',
            6 => 'M',
            9 => 'B',
            12 => 'T',
            15 => 'Q',
        ];
    }

    switch (true) {
        case floatval($number) === 0.0:
            return $precision > 0 ? namespace\format(0, $precision, $maxPrecision) : '0';
        case $number < 0:
            return sprintf('-%s', namespace\to_human_readable(Math\abs($number), $precision, $maxPrecision, $units));
        case $number >= 1e15:
            return sprintf('%s' . end($units), namespace\to_human_readable($number / 1e15, $precision, $maxPrecision, $units));
    }

    $numberExponent = Math\floor(Math\log($number, base: 10));
    $displayExponent = $numberExponent - ($numberExponent % 3);
    $number /= 10 ** $displayExponent;

    return trim(sprintf('%s%s', namespace\format($number, $precision, $maxPrecision), $units[$displayExponent] ?? ''));
}
