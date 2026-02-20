<?php

declare(strict_types=1);

namespace Tempest\Idempotency\Attributes;

use Attribute;
use Tempest\Idempotency\Exceptions\UnsupportedIdempotencyMethod;
use Tempest\Idempotency\Middleware\IdempotencyMiddleware;
use Tempest\Idempotency\SupportedMethod;
use Tempest\Router\Route;
use Tempest\Router\RouteDecorator;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
final readonly class Idempotent implements RouteDecorator
{
    public function __construct(
        public ?int $ttlInSeconds = null,
        public ?int $pendingTtlInSeconds = null,
        public ?bool $requireKey = null,
        public ?string $header = null,
    ) {}

    public function decorate(Route $route): Route
    {
        if (! SupportedMethod::isSupported($route->method)) {
            throw UnsupportedIdempotencyMethod::forMethod($route->method);
        }

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
