<?php

namespace Tempest\Intl;

use Countable;
use Stringable;
use Tempest\Container\GenericContainer;
use Tempest\Intl\Locale;
use Tempest\Intl\Pluralizer\Pluralizer;
use Tempest\Intl\Translator;

use function Tempest\Container\get;

/**
 * Translates the given key with optional arguments.
 */
function translate(string $key, mixed ...$arguments): string
{
    return get(Translator::class)->translate($key, ...$arguments);
}

/**
 * Translates the given key for a specific locale with optional arguments.
 */
function translate_locale(Locale $locale, string $key, mixed ...$arguments): string
{
    return get(Translator::class)->translateForLocale($locale, $key, ...$arguments);
}

/**
 * Converts the given string to its English plural form.
 */
function pluralize(Stringable|string $value, int|array|Countable $count = 2): string
{
    return get(Pluralizer::class)->pluralize($value, $count);
}

/**
 * Converts the given string to its English singular form.
 */
function singularize(Stringable|string $value): string
{
    return get(Pluralizer::class)->singularize($value);
}

/**
 * Converts the last word of the given string to its English singular form.
 */
function singularize_last_word(Stringable|string $value): string
{
    return get(Pluralizer::class)->singularizeLastWord($value);
}

/**
 * Converts the last word of the given string to its English plural form.
 */
function pluralize_last_word(Stringable|string $value, int|array|Countable $count = 2): string
{
    return get(Pluralizer::class)->pluralizeLastWord($value, $count);
}

/**
 * Returns the configured locale from the container, or the system default if not configured.
 */
function current_locale(): Locale
{
    return GenericContainer::instance()?->has(IntlConfig::class)
        ? GenericContainer::instance()->get(IntlConfig::class)->currentLocale
        : Locale::default();
}
