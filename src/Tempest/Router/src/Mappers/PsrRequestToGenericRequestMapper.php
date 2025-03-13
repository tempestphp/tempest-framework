<?php

declare(strict_types=1);

namespace Tempest\Router\Mappers;

use Psr\Http\Message\ServerRequestInterface as PsrRequest;
use Psr\Http\Message\UploadedFileInterface;
use Tempest\Http\Method;
use Tempest\Mapper\Mapper;
use Tempest\Router\GenericRequest;
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

        if (arr($from->getHeader('content-type'))->contains('application/json')) {
            $bodyContents = $from->getBody()->getContents();

            if (json_validate($bodyContents)) {
                $data = [...$data, ...json_decode($bodyContents, true)];
            }
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
            'body' => $data,
            'headers' => $headersAsString,
            'path' => $from->getUri()->getPath(),
            'query' => $query,
            'files' => $uploads,
            ...$data,
            ...$uploads,
        ])
            ->to(GenericRequest::class);
    }
}
