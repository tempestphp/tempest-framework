<?php

declare(strict_types=1);

namespace Tempest\Mapper;

use Tempest\Http\GenericRequest;
use Tempest\Http\Request;
use function Tempest\map;
use Tempest\Support\ArrayHelper;

final readonly class RequestToRequestMapper implements Mapper
{
    public function canMap(object|string $objectOrClass, mixed $data): bool
    {
        return $data instanceof Request && is_a($objectOrClass, Request::class, true);
    }

    public function map(object|string $objectOrClass, mixed $data): array|object
    {
        /** @var Request $origin */
        $origin = $data;

        /** @var class-string<\Tempest\Http\Request> $requestClass */
        $requestClass = is_object($objectOrClass) ? $objectOrClass::class : $objectOrClass;

        $body = (new ArrayHelper())->unwrap($origin->getBody());

        if ($requestClass === Request::class) {
            $requestClass = GenericRequest::class;
        }

        return map([
            'method' => $origin->getMethod(),
            'uri' => $origin->getUri(),
            'body' => $body,
            'headers' => $origin->getHeaders(),
            'path' => $origin->getPath(),
            'query' => $origin->getQuery(),
            ...$body,
        ])->to($requestClass);
    }
}
