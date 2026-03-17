<?php

declare(strict_types=1);

namespace Tempest\Router;

use Attribute;
use Tempest\Http\Method;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_METHOD)]
final class Trace implements Route
{
    public Method $method = Method::TRACE;

    /**
     * @param class-string<HttpMiddleware>[] $middleware Middleware specific to this route.
     * @param class-string<HttpMiddleware>[] $without Middleware to remove from this route.
     */
    public function __construct(
        public string $uri,
        public array $middleware = [],
        public array $without = [],
    ) {}
}
