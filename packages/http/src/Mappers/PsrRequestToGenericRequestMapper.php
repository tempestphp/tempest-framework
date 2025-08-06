<?php

declare(strict_types=1);

namespace Tempest\Http\Mappers;

use Psr\Http\Message\ServerRequestInterface as PsrRequest;
use Psr\Http\Message\UploadedFileInterface;
use Tempest\Cryptography\Encryption\Encrypter;
use Tempest\Http\Cookie\Cookie;
use Tempest\Http\GenericRequest;
use Tempest\Http\Method;
use Tempest\Http\RequestHeaders;
use Tempest\Http\Upload;
use Tempest\Mapper\Mapper;
use Tempest\Support\Arr;

use function Tempest\map;
use function Tempest\Support\arr;

final readonly class PsrRequestToGenericRequestMapper implements Mapper
{
    public function __construct(
        private Encrypter $encrypter,
    ) {}

    public function canMap(mixed $from, mixed $to): bool
    {
        return false;
    }

    public function map(mixed $from, mixed $to): GenericRequest
    {
        /** @var PsrRequest $from */
        $data = (array) $from->getParsedBody();
        $raw = $from->getBody()->getContents();

        if (arr($from->getHeader('content-type'))->hasValue('application/json') && json_validate($raw)) {
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
            'cookies' => Arr\map_iterable(
                array: $_COOKIE,
                map: fn (string $value, string $key) => new Cookie(
                    key: $key,
                    value: $this->encrypter->decrypt($value),
                ),
            ),
        ])
            ->to(GenericRequest::class);
    }
}
