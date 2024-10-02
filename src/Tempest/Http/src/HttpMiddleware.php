<?php

declare(strict_types=1);

namespace Tempest\Http;

interface HttpMiddleware
{
    /** @param callable(\Tempest\Http\Request $request): \Tempest\Http\Response $next */
    public function __invoke(Request $request, callable $next): Response;
}
