<?php

declare(strict_types=1);

namespace Tempest\Http;

use Attribute;

#[Attribute]
final class Post extends Route
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
            method: Method::POST,
            middleware: $middleware,
        );
    }
}
