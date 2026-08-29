<?php

declare(strict_types=1);

namespace Tempest\RateLimit\Http;

use Tempest\Container\Container;
use Tempest\Http\Request;
use Tempest\RateLimit\RateLimit;
use Tempest\Router\RouteDecorator;

/**
 * An attribute subjecting the route it decorates to rate limits. Implementing it puts
 * {@see ThrottleMiddleware} on the route and has the middleware collect the attribute.
 */
interface Throttles extends RouteDecorator
{
    /**
     * Returns the limits this attribute subjects the specified request to. The middleware scopes them
     * through {@see ThrottleCounterKey} before consuming any.
     *
     * @return RateLimit[]
     */
    public function resolveLimits(Request $request, Container $container): array;
}
