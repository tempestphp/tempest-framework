<?php

declare(strict_types=1);

namespace Tempest\Router;

use Attribute;
use Tempest\Http\Method;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_METHOD)]
final class Trace extends Route
{
    public function __construct(
        string $uri,

        /**
         * @template MiddlewareClass of \Tempest\Router\HttpMiddleware
         * @var class-string<MiddlewareClass>[] $middleware
         */
        array $middleware = [],
    ) {
        parent::__construct(
            uri: $uri,
            method: Method::TRACE,
            middleware: $middleware,
        );
    }
}
