<?php

declare(strict_types=1);

namespace Tempest\Mapper;

use Psr\Http\Message\RequestInterface as PsrRequest;
use Tempest\Http\GenericRequest;
use Tempest\Http\Method;
use Tempest\Http\Request;
use function Tempest\map;
use Tempest\Validation\Validator;

final readonly class PsrRequestToRequestMapper implements Mapper
{
    public function canMap(object|string $to, mixed $from): bool
    {
        return $from instanceof PsrRequest && is_a($to, Request::class, true);
    }

    public function map(object|string $to, mixed $from): array|object
    {
        /** @var PsrRequest $origin */
        $origin = $from;

        /** @var class-string<\Tempest\Http\Request> $requestClass */
        $requestClass = is_object($to) ? $to::class : $to;

        if ($requestClass === Request::class) {
            $requestClass = GenericRequest::class;
        }

        $from = [];

        $newRequest = map([
            'method' => Method::from($origin->getMethod()),
            'uri' => $origin->getUri(),
            'body' => (string) $origin->getBody(),
            'headers' => $origin->getHeaders(),
            'path' => $origin->getUri()->getPath(),
            'query' => $origin->getUri()->getQuery(),
            ...$from,
        ])->to($requestClass);

        $validator = new Validator();
        $validator->validate($newRequest);

        return $newRequest;
    }
}
