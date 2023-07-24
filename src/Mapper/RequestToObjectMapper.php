<?php

declare(strict_types=1);

namespace Tempest\Mapper;

use Tempest\Interfaces\Mapper;
use Tempest\Interfaces\Request;

use function Tempest\map;

final readonly class RequestToObjectMapper implements Mapper
{
    public function canMap(object|string $objectOrClass, mixed $data): bool
    {
        return $data instanceof Request;
    }

    public function map(object|string $objectOrClass, mixed $data): array|object
    {
        /** @var \Tempest\Interfaces\Request $data */
        return map($data->getBody())->to($objectOrClass);
    }
}
