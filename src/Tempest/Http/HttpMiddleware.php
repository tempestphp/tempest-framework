<?php

declare(strict_types=1);

namespace Tempest\Http;

interface HttpMiddleware
{
    public function __invoke(Request $request, callable $next): Response;
}
