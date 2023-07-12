<?php

namespace Tempest\Http;

trait BaseResponse
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
}
