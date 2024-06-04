<?php

declare(strict_types=1);

namespace Tempest\Mapper\Mappers;

use Tempest\Mapper\Mapper;
use Tempest\Mapper\MapTo;

final readonly class ArrayToJsonMapper implements Mapper
{
    public function canMap(mixed $from, mixed $to): bool
    {
        return $to === MapTo::JSON && is_array($from);
    }

    public function map(mixed $from, mixed $to): string
    {
        return json_encode($from);
    }
}
