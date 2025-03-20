<?php

declare(strict_types=1);

namespace Tempest\Mapper\Mappers;

use Stringable;
use Tempest\Mapper\Mapper;

final readonly class StringToStringableMapper implements Mapper
{
    public function canMap(mixed $from, mixed $to): bool
    {
        if (! is_a($to, Stringable::class, allow_string: true)) {
            return false;
        }

        return $from instanceof Stringable || is_string($from);
    }

    public function map(mixed $from, mixed $to): Stringable
    {
        return new $to($from);
    }
}
