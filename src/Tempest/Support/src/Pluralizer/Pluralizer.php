<?php

declare(strict_types=1);

namespace Tempest\Support\Pluralizer;

use Countable;

interface Pluralizer
{
    public function pluralize(string $value, int|array|Countable $count = 2): string;

    public function singularize(string $value): string;
}
