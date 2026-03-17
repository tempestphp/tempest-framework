<?php

declare(strict_types=1);

namespace Tempest\Idempotency\Attributes;

use Attribute;
use Tempest\Idempotency\Middleware\IdempotencyMiddleware;
use Tempest\Router\Route;
use Tempest\Router\RouteDecorator;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
final readonly class Idempotent implements RouteDecorator
{
    public function __construct(
        public ?int $ttlInSeconds = null,
        public ?int $pendingTtlInSeconds = null,
    ) {}

    public function decorate(Route $route): Route
    {
        if (in_array(IdempotencyMiddleware::class, $route->middleware, true)) {
            return $route;
        }

        $route->middleware = [
            ...$route->middleware,
            IdempotencyMiddleware::class,
        ];

        return $route;
    }
}
