<?php

declare(strict_types=1);

namespace Tempest\Mapper\Mappers;

use Tempest\Mapper\Mapper;
use Tempest\Support\Json;

final readonly class JsonToArrayMapper implements Mapper
{
    public function canMap(mixed $from, mixed $to): bool
    {
        return is_string($from) && Json\is_valid($from);
    }

    public function map(mixed $from, mixed $to): array
    {
        return Json\decode($from, true);
    }
}
