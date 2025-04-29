<?php

declare(strict_types=1);

namespace Tempest\Router;

use Closure;
use Tempest\Http\Request;
use Tempest\Http\Response;

final readonly class HttpMiddlewareCallable
{
    public function __construct(
        private Closure $closure,
    ) {}

    public function __invoke(Request $request): Response
    {
        return ($this->closure)($request);
    }
}
