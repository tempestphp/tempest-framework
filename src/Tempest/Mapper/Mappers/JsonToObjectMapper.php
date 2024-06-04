<?php

declare(strict_types=1);

namespace Tempest\Mapper\Mappers;

use ReflectionClass;
use Tempest\Mapper\Mapper;
use Tempest\Mapper\MapTo;
use Throwable;
use function Tempest\map;

final readonly class JsonToObjectMapper implements Mapper
{
    public function canMap(mixed $from, mixed $to): bool
    {
        if (! is_string($from)) {
            return false;
        }

        if (! json_validate($from)) {
            return false;
        }

        try {
            $class = new ReflectionClass($to);

            return $class->isInstantiable();
        } catch (Throwable) {
            return false;
        }
    }

    public function map(mixed $from, mixed $to): object
    {
        return map(
            map($from)->to(MapTo::ARRAY)
        )->to($to);
    }
}
