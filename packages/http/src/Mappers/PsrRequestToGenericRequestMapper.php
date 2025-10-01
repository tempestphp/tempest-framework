<?php

declare(strict_types=1);

namespace Tempest\Http\Mappers;

use Psr\Http\Message\ServerRequestInterface as PsrRequest;
use Psr\Http\Message\UploadedFileInterface;
use Tempest\Cryptography\Encryption\Encrypter;
use Tempest\Http\Cookie\Cookie;
use Tempest\Http\Cookie\CookieManager;
use Tempest\Http\GenericRequest;
use Tempest\Http\Method;
use Tempest\Http\RequestHeaders;
use Tempest\Http\Upload;
use Tempest\Mapper\Mapper;
use Tempest\Support\Arr;
use Throwable;

use function Tempest\map;
use function Tempest\Support\arr;

final readonly class PsrRequestToGenericRequestMapper implements Mapper
{
    public function __construct(
        private Encrypter $encrypter,
        private CookieManager $cookies,
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
            'method' => $this->requestMethod($from, $data),
            'uri' => (string) $from->getUri(),
            'raw' => $raw,
            'body' => $data,
            'headers' => RequestHeaders::normalizeFromArray($headersAsString),
            'path' => $from->getUri()->getPath(),
            'query' => $query,
            'files' => $uploads,
            'cookies' => Arr\filter(Arr\map_iterable(
                array: $_COOKIE,
                map: function (string $value, string $key) {
                    try {
                        return new Cookie(
                            key: $key,
                            value: $this->encrypter->decrypt($value),
                        );
                    } catch (Throwable) {
                        $this->cookies->remove($key);

                        return null;
                    }
                },
            )),
        ])
            ->to(GenericRequest::class);
    }

    private function requestMethod(PsrRequest $request, array $data): Method
    {
        $originalMethod = Method::from($request->getMethod());
        if ($originalMethod !== Method::POST) {
            return $originalMethod;
        }

        if (! isset($data['_method'])) {
            return $originalMethod;
        }

        return Method::trySpoofingFrom($data['_method']) ?? $originalMethod;
    }
}
