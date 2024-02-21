<?php

declare(strict_types=1);

namespace Tempest\Http;

interface Request
{
    public function getMethod(): Method;

    public function getUri(): string;

    public function getBody(): array;

    public function getPath(): string;

    public function getQuery(): ?string;
}
