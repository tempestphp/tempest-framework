<?php

namespace Tempest\Interfaces;

use Tempest\Http\Status;

interface Response
{
    public function getStatus(): Status;

    public function getHeaders(): array;

    public function getBody(): string;

    public function body(string $body): self;

    public function ok(): self;

    public function notFound(): self;
}
