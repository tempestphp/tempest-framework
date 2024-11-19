<?php

declare(strict_types=1);

namespace Tempest\Mapper\Mappers;

use function Tempest\map;
use Tempest\Mapper\Mapper;
use Tempest\Mapper\MapTo;
use Tempest\Reflection\ClassReflector;
use Throwable;
use function Tempest\path;

final readonly class JsonFileToObjectMapper implements Mapper
{
    public function canMap(mixed $from, mixed $to): bool
    {
        if (! is_string($from)) {
            return false;
        }

        $path = path($from);

        return $path->exists() && $path->extension() === 'json';
    }

    public function map(mixed $from, mixed $to): array
    {
        return map(json_decode(file_get_contents($from), associative: true))->collection()->to($to);
    }
}
