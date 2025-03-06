<?php

declare(strict_types=1);

namespace Tempest\Support\Pluralizer;

use Countable;
use Stringable;

interface Pluralizer
{
    public function pluralize(Stringable|string $value, int|array|Countable $count = 2): string;

    public function singularize(Stringable|string $value): string;
}
