<?php

declare(strict_types=1);

namespace Tempest\Router;

use Attribute;
use Tempest\Http\Method;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_METHOD)]
final readonly class Get implements Route
{
    public Method $method;

    /**
     * @param class-string<HttpMiddleware>[] $middleware
     */
    public function __construct(
        public string $uri,
        public array $middleware = [],
    ) {
        $this->method = Method::GET;
    }
}
