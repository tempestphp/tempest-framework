<?php

declare(strict_types=1);

namespace Tempest\Mapper;

use Laminas\Diactoros\ServerRequest;
use Psr\Http\Message\ServerRequestInterface as PsrRequest;
use Tempest\Http\Request;

final readonly class RequestToPsrRequestMapper implements Mapper
{
    public function canMap(mixed $from, object|string $to): bool
    {
        return $from instanceof Request && is_a($to, PsrRequest::class, true);
    }

    public function map(mixed $from, object|string $to): PsrRequest
    {
        /** @var Request $from */

        return new ServerRequest(
            uri: $from->getUri(),
            method: $from->getMethod()->value,
            headers: $from->getHeaders(),
            cookieParams: $from->getCookies(),
            queryParams: $from->getQuery(),
            parsedBody: $from->getBody(),
        );
    }
}
