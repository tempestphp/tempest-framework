<?php

declare(strict_types=1);

namespace Tempest\Mapper;

use Psr\Http\Message\RequestInterface as PsrRequest;
use Tempest\Http\Request;

final readonly class RequestToPsrRequestMapper implements Mapper
{
    public function canMap(object|string $to, mixed $from): bool
    {
        return $from instanceof Request && is_a($to, PsrRequest::class, true);
    }

    public function map(object|string $to, mixed $from): array|object
    {
        /** @var Request $origin */
        $origin = $from;

        return new \Laminas\Diactoros\Request(
            uri: $origin->getUri(),
            method: $origin->getMethod()->value,
            headers: $origin->getHeaders(),
        );
    }
}
