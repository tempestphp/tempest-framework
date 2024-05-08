<?php

namespace Tempest\Mapper\Mappers;

use Tempest\Mapper\Mapper;
use Tempest\Mapper\To;

final readonly class ArrayToJsonMapper implements Mapper
{
    public function canMap(mixed $from, mixed $to): bool
    {
        return is_array($from) && $to === To::JSON;
    }

    public function map(mixed $from, mixed $to): string
    {
        return json_encode($from);
    }
}