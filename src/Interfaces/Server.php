<?php

namespace Tempest\Interfaces;

use Tempest\Route\Method;

interface Server
{
    public function getMethod(): Method;

    public function getUri(): string;

    public function getBody(): array;
}