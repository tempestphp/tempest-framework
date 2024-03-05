<?php

declare(strict_types=1);

namespace Tempest\Mapper;

use Psr\Http\Message\RequestInterface as PsrRequest;
use Tempest\Http\Request;

final readonly class RequestToPsrRequestMapper implements Mapper
{
    public function canMap(object|string $objectOrClass, mixed $data): bool
    {
        return $data instanceof Request && is_a($objectOrClass, PsrRequest::class, true);
    }

    public function map(object|string $objectOrClass, mixed $data): array|object
    {
        /** @var Request $origin */
        $origin = $data;

        return new \Laminas\Diactoros\Request(
            uri: $origin->getUri(),
            method: $origin->getMethod()->value,
            headers: $origin->getHeaders(),
        );
    }
}
