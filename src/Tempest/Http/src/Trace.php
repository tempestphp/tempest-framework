<?php

declare(strict_types=1);

namespace Tempest\Http;

use Attribute;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_METHOD)]
final class Trace extends Route
{
    public function __construct(
        string $uri,

        /**
         * @template MiddlewareClass of \Tempest\Http\HttpMiddleware
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
