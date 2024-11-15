<?php

declare(strict_types=1);

namespace Tempest\Http\Mappers;

use Psr\Http\Message\ServerRequestInterface as PsrRequest;
use Psr\Http\Message\UploadedFileInterface;
use Tempest\Http\GenericRequest;
use Tempest\Http\Method;
use Tempest\Http\Request;
use Tempest\Http\Upload;
use function Tempest\map;
use Tempest\Mapper\Mapper;
use Tempest\Validation\Validator;

final readonly class PsrRequestToRequestMapper implements Mapper
{
    public function canMap(mixed $from, mixed $to): bool
    {
        return $from instanceof PsrRequest && is_a($to, Request::class, true);
    }

    public function map(mixed $from, mixed $to): array|object
    {
        /** @var PsrRequest $from */
        /** @var class-string<\Tempest\Http\Request> $requestClass */
        $requestClass = is_object($to) ? $to::class : $to;

        if ($requestClass === Request::class) {
            $requestClass = GenericRequest::class;
        }

        $data = (array)$from->getParsedBody();

        $headersAsString = array_map(
            fn (array $items) => implode(',', $items),
            $from->getHeaders(),
        );

        parse_str($from->getUri()->getQuery(), $query);

        $uploads = array_map(
            fn (UploadedFileInterface $uploadedFile) => new Upload($uploadedFile),
            $from->getUploadedFiles(),
        );

        $newRequest = map([
            'method' => Method::from($from->getMethod()),
            'uri' => (string)$from->getUri(),
            'body' => $data,
            'headers' => $headersAsString,
            'path' => $from->getUri()->getPath(),
            'query' => $query,
            'files' => $uploads,
            ...$data,
            ...$uploads,
        ])->to($requestClass);

        $validator = new Validator();
        $validator->validate($newRequest);

        return $newRequest;
    }
}
