<?php

declare(strict_types=1);

namespace Tempest\Mapper\Mappers;

use Tempest\Mapper\Mapper;
use Tempest\Support\Filesystem;

use function Tempest\map;
use function Tempest\Support\path;

final readonly class JsonFileToObjectMapper implements Mapper
{
    public function canMap(mixed $from, mixed $to): bool
    {
        if (! is_string($from)) {
            return false;
        }

        $path = path($from);

        return $path->exists() && $path->extension()->equals('json');
    }

    public function map(mixed $from, mixed $to): array
    {
        return map(Filesystem\read_json($from))->collection()->to($to);
    }
}
