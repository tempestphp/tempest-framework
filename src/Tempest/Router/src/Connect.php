<?php

declare(strict_types=1);

namespace Tempest\Router;

use Attribute;
use Tempest\Http\Method;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_METHOD)]
final class Connect implements Route
{
    use IsRoute;

    /**
     * @param class-string<HttpMiddleware>[] $middleware
     */
    public function __construct(
        string $uri,
        array $middleware = [],
    ) {
        $this->uri = $uri;
        $this->method = Method::CONNECT;
        $this->middleware = $middleware;
    }
}
