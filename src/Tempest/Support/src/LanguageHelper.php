<?php

declare(strict_types=1);

namespace Tempest\Support;

use Countable;
use Tempest\Support\Pluralizer\Pluralizer;
use function Tempest\get;

final class LanguageHelper
{
    /**
     * Converts the given string to its English plural form.
     */
    public static function pluralize(string $value, int|array|Countable $count = 2): string
    {
        return get(Pluralizer::class)->pluralize($value, $count);
    }

    /**
     * Converts the given string to its English singular form.
     */
    public static function singularize(string $value): string
    {
        return get(Pluralizer::class)->singularize($value);
    }
}
