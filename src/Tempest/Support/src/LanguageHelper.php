<?php

declare(strict_types=1);

namespace Tempest\Support;

use Countable;
use function Tempest\get;
use Tempest\Support\Pluralizer\Pluralizer;

final class LanguageHelper
{
    /**
     * @param string[] $parts
     */
    public static function join(array $parts): string
    {
        $last = array_pop($parts);

        if ($parts) {
            return implode(', ', $parts) . ' ' . 'and' . ' ' . $last;
        }

        return $last;
    }

    public static function pluralize(string $value, int|array|Countable $count = 2): string
    {
        return get(Pluralizer::class)->pluralize($value, $count);
    }

    public static function singularize(string $value): string
    {
        return get(Pluralizer::class)->singularize($value);
    }
}
