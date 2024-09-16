<?php

declare(strict_types=1);

namespace Tempest\Router;

use Attribute;
use Tempest\Http\Method;

#[Attribute]
final class Get extends Route
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
            method: Method::GET,
            middleware: $middleware,
        );
    }
}
