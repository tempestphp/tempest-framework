<?php

declare(strict_types=1);

namespace Tempest\Intl\Pluralizer;

use Countable;
use Stringable;

interface Pluralizer
{
    /**
     * Converts the given string to its English plural form.
     */
    public function pluralize(Stringable|string $value, int|array|Countable $count = 2): string;

    /**
     * Converts the given string to its English singular form.
     */
    public function singularize(Stringable|string $value): string;

    /**
     * Converts the last word of the given string to its English singular form.
     */
    public function singularizeLastWord(Stringable|string $value): string;

    /**
     * Converts the last word of the given string to its English plural form.
     */
    public function pluralizeLastWord(Stringable|string $value, int|array|Countable $count = 2): string;
}
