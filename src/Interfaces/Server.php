<?php

namespace Tempest\Interfaces;

use Tempest\Http\Method;

interface Server
{
    public function getMethod(): Method;

    public function getUri(): string;

    public function getBody(): array;
}
