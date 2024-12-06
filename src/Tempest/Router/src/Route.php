<?php

declare(strict_types=1);

namespace Tempest\Router;

use Tempest\Http\Method;

interface Route
{
    public Method $method { get; }

    public string $uri { get; }

    public array $middleware { get; }
}
