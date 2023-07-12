<?php

namespace Tempest\Http;

trait BaseResponse
{
    public function __construct(
        private Status $status,
        private string $body = '',
        private array $headers = [],
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

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function header(string $key, string $value): self
    {
        $this->headers[$key] = $value;

        return $this;
    }

    public function body(string $body): self
    {
        $this->body = $body;

        return $this;
    }

    public function ok(): self
    {
        $this->status = Status::HTTP_200;

        return $this;
    }

    public function notFound(): self
    {
        $this->status = Status::HTTP_404;

        return $this;
    }

    public function status(Status $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function redirect(string $to): self
    {
        return $this
            ->header('Location', $to)
            ->status(Status::HTTP_302);
    }
}
