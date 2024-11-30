<?php

declare(strict_types=1);

namespace Tempest\Router;

use Tempest\Http\Method;

interface RouteInterface
{
    public function method(): Method;
    public function uri(): string;
    /** @return HttpMiddleware[] */
    public function middleware(): array;
}
