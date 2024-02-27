<?php

declare(strict_types=1);

namespace Tempest\Http;

interface Server
{
    public function getMethod(): Method;

    public function getUri(): string;

    public function getBody(): array;

    public function getHeaders(): array;
}
