<?php

declare(strict_types=1);

namespace Tempest\Router;

use Tempest\Http\Request;
use Tempest\Http\Response;

interface HttpMiddleware
{
    public function __invoke(Request $request, HttpMiddlewareCallable $next): Response;
}
