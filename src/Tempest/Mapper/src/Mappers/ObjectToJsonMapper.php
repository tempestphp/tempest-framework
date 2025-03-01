<?php

declare(strict_types=1);

namespace Tempest\Mapper\Mappers;

use Tempest\Mapper\Mapper;
use Tempest\Mapper\MapTo;
use function Tempest\map;

final readonly class ObjectToJsonMapper implements Mapper
{
    public function canMap(mixed $from, mixed $to): bool
    {
        return false;
    }

    public function map(mixed $from, mixed $to): string
    {
        return map(map($from)->toArray())->toJson();
    }
}
