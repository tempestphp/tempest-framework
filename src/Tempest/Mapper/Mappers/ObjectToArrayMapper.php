<?php

declare(strict_types=1);

namespace Tempest\Mapper\Mappers;

use Tempest\Mapper\Mapper;
use Tempest\Mapper\MapTo;

final readonly class ObjectToArrayMapper implements Mapper
{
    public function canMap(mixed $from, mixed $to): bool
    {
        return $to === MapTo::ARRAY && is_object($from);
    }

    public function map(mixed $from, mixed $to): array
    {
        return (array) $from;
    }
}
