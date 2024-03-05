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
    public function canMap(object|string $objectOrClass, mixed $data): bool
    {
        return $data instanceof PsrRequest && is_a($objectOrClass, Request::class, true);
    }

    public function map(object|string $objectOrClass, mixed $data): array|object
    {
        /** @var PsrRequest $origin */
        $origin = $data;

        /** @var class-string<\Tempest\Http\Request> $requestClass */
        $requestClass = is_object($objectOrClass) ? $objectOrClass::class : $objectOrClass;

        if ($requestClass === Request::class) {
            $requestClass = GenericRequest::class;
        }

        $data = [];

        $newRequest = map([
            'method' => Method::from($origin->getMethod()),
            'uri' => $origin->getUri(),
            'body' => (string) $origin->getBody(),
            'headers' => $origin->getHeaders(),
            'path' => $origin->getUri()->getPath(),
            'query' => $origin->getUri()->getQuery(),
            ...$data,
        ])->to($requestClass);

        $validator = new Validator();
        $validator->validate($newRequest);

        return $newRequest;
    }
}
