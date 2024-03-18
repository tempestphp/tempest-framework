<?php

declare(strict_types=1);

namespace Tempest\Mapper;

use DateTimeInterface;
use Stringable;
use Tempest\ORM\QueryValue;

final readonly class ValueToQueryValueMapper implements Mapper
{
    public function canMap(mixed $from, object|string $to): bool
    {
        return $to === QueryValue::class && (is_scalar($from) || $from instanceof Stringable || $from instanceof DateTimeInterface);
    }

    public function map(mixed $from, object|string $to): array|object
    {
        $value = $from;

        if ($value instanceof DateTimeInterface) {
            $value = $value->format('Y-m-d H:i:s');
        }

        if ($value instanceof Stringable) {
            $value = (string) $value;
        }

        return new QueryValue($value);
    }
}
