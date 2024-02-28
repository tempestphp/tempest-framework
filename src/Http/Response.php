<?php

declare(strict_types=1);

namespace Tempest\Http;

interface Response
{
    public function getStatus(): Status;

    public function getHeaders(): array;

    public function getBody(): string|array;

    public function body(string $body): self;

    public function header(string $key, string $value): self;

    public function ok(): self;

    public function notFound(): self;

    public function redirect(string $to): self;

    public function status(Status $status): self;
}
