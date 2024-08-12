<?php

declare(strict_types=1);

namespace Tempest\Mapper\Mappers;

use function Tempest\map;
use Tempest\Mapper\Mapper;
use Tempest\Mapper\MapTo;
use Tempest\Support\Reflection\ClassReflector;
use Throwable;

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
            $class = new ClassReflector($to);

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
