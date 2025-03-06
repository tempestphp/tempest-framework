<?php

declare(strict_types=1);

namespace Tempest\Support\Language {
    use Countable;
    use Stringable;
    use Tempest\Support\Pluralizer\Pluralizer;
    use function Tempest\get;

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
}
