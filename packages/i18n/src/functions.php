<?php

namespace Tempest\Internationalization;

use Tempest\Internationalization\Translator;
use Tempest\Support\Language\Locale;

use function Tempest\get;

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
