<?php

declare(strict_types=1);

namespace Tempest\Http;

use Closure;

final readonly class HttpMiddlewareCallable
{
    public function __construct(
        private Closure $closure,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        return ($this->closure)($request);
    }
}
