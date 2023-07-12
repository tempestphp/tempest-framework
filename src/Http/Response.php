<?php

namespace Tempest\Http;

final readonly class Response
{
    public function __construct(
        private Status $status,
        private string $body = '',
    ) {
    }

    public function getStatus(): Status
    {
        return $this->status;
    }

    public function getBody(): string
    {
        return $this->body;
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
