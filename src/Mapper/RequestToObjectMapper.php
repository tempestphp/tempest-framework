<?php

declare(strict_types=1);

namespace Tempest\Mapper;

use Tempest\Http\Request;
use function Tempest\map;

final readonly class RequestToObjectMapper implements Mapper
{
    public function canMap(mixed $from, object|string $to): bool
    {
        return $from instanceof Request;
    }

    public function map(mixed $from, object|string $to): array|object
    {
        /** @var Request $from */
        return map($from->getBody())->to($to);
    }
}
