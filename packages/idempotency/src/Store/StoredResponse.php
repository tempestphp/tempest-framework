<?php

declare(strict_types=1);

namespace Tempest\Idempotency\Store;

use Generator;
use JsonSerializable;
use Tempest\Http\GenericResponse;
use Tempest\Http\Response;
use Tempest\Http\Status;
use Tempest\View\View;
use Throwable;

use function Tempest\Support\Json\decode;
use function Tempest\Support\Json\encode;

final readonly class StoredResponse
{
    public function __construct(
        public int $statusCode,
        /** @var array<string, array<int, string>> */
        public array $headers,
        public ?string $serializedBody,
    ) {}

    public static function fromResponse(Response $response): self
    {
        $headers = [];

        foreach ($response->headers as $header) {
            $headers[$header->name] = array_map(
                callback: static fn (mixed $value): string => (string) $value,
                array: $header->values,
            );
        }

        return new self(
            statusCode: $response->status->value,
            headers: $headers,
            serializedBody: self::serializeBody($response->body),
        );
    }

    public function toResponse(): Response
    {
        return new GenericResponse(
            status: Status::fromCode($this->statusCode),
            body: $this->unserializeBody(),
            headers: $this->headers,
        );
    }

    private static function serializeBody(mixed $body): ?string
    {
        if ($body === null) {
            return null;
        }

        if ($body instanceof JsonSerializable) {
            $encodedBody = self::tryJsonEncode($body);

            if ($encodedBody !== null) {
                return 'json:' . base64_encode($encodedBody);
            }
        }

        if ($body instanceof Generator || $body instanceof View) {
            return 'string:' . base64_encode(get_debug_type($body));
        }

        try {
            return 'php:' . base64_encode(serialize($body));
        } catch (Throwable) {
            $encodedBody = self::tryJsonEncode($body);

            if ($encodedBody !== null) {
                return 'json:' . base64_encode($encodedBody);
            }

            return 'string:' . base64_encode(get_debug_type($body));
        }
    }

    private function unserializeBody(): mixed
    {
        if ($this->serializedBody === null) {
            return null;
        }

        if (str_starts_with($this->serializedBody, 'php:')) {
            try {
                $decoded = base64_decode(substr($this->serializedBody, 4), true);

                if ($decoded === false) {
                    return null;
                }

                return $this->normalizeBody(unserialize($decoded, ['allowed_classes' => false]));
            } catch (Throwable) {
                return null;
            }
        }

        if (str_starts_with($this->serializedBody, 'json:')) {
            try {
                $decoded = base64_decode(substr($this->serializedBody, 5), true);

                if ($decoded === false) {
                    return null;
                }

                return $this->normalizeBody(decode($decoded));
            } catch (Throwable) {
                return null;
            }
        }

        if (str_starts_with($this->serializedBody, 'string:')) {
            $decoded = base64_decode(substr($this->serializedBody, 7), true);

            return $decoded === false ? null : $decoded;
        }

        try {
            return $this->normalizeBody(unserialize($this->serializedBody, ['allowed_classes' => false]));
        } catch (Throwable) {
            return null;
        }
    }

    private function normalizeBody(mixed $body): Generator|View|string|array|JsonSerializable|null
    {
        if ($body === null || is_string($body) || is_array($body) || $body instanceof Generator || $body instanceof View || $body instanceof JsonSerializable) {
            return $body;
        }

        if (is_bool($body)) {
            return $body ? 'true' : 'false';
        }

        if (is_int($body) || is_float($body)) {
            return (string) $body;
        }

        return get_debug_type($body);
    }

    private static function tryJsonEncode(mixed $body): ?string
    {
        try {
            return encode($body);
        } catch (Throwable) {
            return null;
        }
    }
}
