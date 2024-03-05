<?php

declare(strict_types=1);

namespace Tempest\Mapper;

use Tempest\Http\Request;
use function Tempest\map;

final readonly class RequestToObjectMapper implements Mapper
{
    public function canMap(object|string $to, mixed $from): bool
    {
        return $from instanceof Request;
    }

    public function map(object|string $to, mixed $from): array|object
    {
        /** @var Request $from */
        return map($from->getBody())->to($to);
    }
}
