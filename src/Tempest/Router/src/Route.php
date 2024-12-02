<?php

declare(strict_types=1);

namespace Tempest\Router;

use Tempest\Http\Method;

interface Route
{
    public function method(): Method;

    public function uri(): string;

    /** @return class-string<HttpMiddleware>[] */
    public function middleware(): array;
}
