<?php

declare(strict_types=1);

namespace Tempest\Mapper\Mappers;

use Tempest\Mapper\Mapper;
use Tempest\Mapper\MapTo;

final readonly class JsonToArrayMapper implements Mapper
{
    public function canMap(mixed $from, mixed $to): bool
    {
        return $to === MapTo::ARRAY && is_string($from) && json_validate($from);
    }

    public function map(mixed $from, mixed $to): array
    {
        return json_decode($from, true);
    }
}
