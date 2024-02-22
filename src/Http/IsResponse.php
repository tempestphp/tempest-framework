<?php

declare(strict_types=1);

namespace Tempest\Http;

trait IsResponse
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
        $this->status = Status::OK;

        return $this;
    }

    public function notFound(): self
    {
        $this->status = Status::NOT_FOUND;

        return $this;
    }

    public function status(Status $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function sendHeaders(): self
    {
        // TODO: Handle SAPI/FastCGI

        if (headers_sent()) {
            return $this;
        }

        foreach ($this->getHeaders() as $key => $value) {
            header("{$key}: {$value}");
        }

        /**
         * TODO: Is there a reason to manually set the headers
         * vs this http_response_code helper?
         */
        http_response_code($this->status->value);

        return $this;
    }

    public function sendContent(): self
    {
        // TODO: Should we be checking if content has already been sent?

        echo $this->getBody();

        return $this;
    }

    public function send(): self
    {
        ob_start();

        $this->sendHeaders();
        $this->sendContent();

        ob_end_flush();

        return $this;
    }

    public function redirect(string $to): self
    {
        return $this
            ->header('Location', $to)
            ->status(Status::FOUND);
    }
}
