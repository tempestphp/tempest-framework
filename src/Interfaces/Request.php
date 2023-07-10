<?php

namespace Tempest\Interfaces;

use Tempest\Route\Method;

interface Request
{
    public function getMethod(): Method;

    public function getUri(): string;

    public function getBody(): array;

    public function getPath(): string;

    public function getQuery(): ?string;
}