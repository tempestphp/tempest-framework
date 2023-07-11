<?php

namespace Tempest\Http;

final readonly class Response
{
    public function __construct(
        public Status $status,
        public string $body = '',
    ) {
    }

    public static function notFound(): self
    {
        return new self(Status::HTTP_404);
    }

    public static function ok(string $body = ''): self
    {
        return new self(Status::HTTP_200, $body);
    }
}
