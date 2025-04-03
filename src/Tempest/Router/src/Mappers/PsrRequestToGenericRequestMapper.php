<?php

declare(strict_types=1);

namespace Tempest\Router\Mappers;

use Psr\Http\Message\ServerRequestInterface as PsrRequest;
use Psr\Http\Message\UploadedFileInterface;
use Tempest\Http\Method;
use Tempest\Mapper\Mapper;
use Tempest\Router\GenericRequest;
use Tempest\Router\RequestHeaders;
use Tempest\Router\Upload;

use function Tempest\map;
use function Tempest\Support\arr;

final readonly class PsrRequestToGenericRequestMapper implements Mapper
{
    public function canMap(mixed $from, mixed $to): bool
    {
        return false;
    }

    public function map(mixed $from, mixed $to): GenericRequest
    {
        /** @var PsrRequest $from */
        $data = (array) $from->getParsedBody();
        $raw = $from->getBody()->getContents();

        if (arr($from->getHeader('content-type'))->contains('application/json') && json_validate($raw)) {
            $data = [...$data, ...json_decode($raw, associative: true)];
        }

        $headersAsString = array_map(
            fn (array $items) => implode(',', $items),
            $from->getHeaders(),
        );

        parse_str($from->getUri()->getQuery(), $query);

        $uploads = array_map(
            fn (UploadedFileInterface $uploadedFile) => new Upload($uploadedFile),
            $from->getUploadedFiles(),
        );

        return map([
            'method' => Method::from($from->getMethod()),
            'uri' => (string) $from->getUri(),
            'raw' => $raw,
            'body' => $data,
            'headers' => RequestHeaders::normalizeFromArray($headersAsString),
            'path' => $from->getUri()->getPath(),
            'query' => $query,
            'files' => $uploads,
            ...$data,
            ...$uploads,
        ])
            ->to(GenericRequest::class);
    }
}
