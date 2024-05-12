<?php

declare(strict_types=1);

namespace Tempest\Http\Mappers;

use Tempest\Http\Request;
use function Tempest\map;
use Tempest\Mapper\Mapper;

final readonly class RequestToObjectMapper implements Mapper
{
    public function canMap(mixed $from, mixed $to): bool
    {
        return $from instanceof Request;
    }

    public function map(mixed $from, mixed $to): array|object
    {
        /** @var Request $from */
        return map($from->getBody())->to($to);
    }
}
