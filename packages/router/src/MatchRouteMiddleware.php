<?php

namespace Tempest\Router;

use Tempest\Container\Container;
use Tempest\Http\GenericRequest;
use Tempest\Http\Method;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Http\Responses\NotFound;
use Tempest\Router\Routing\Matching\RouteMatcher;
use Tempest\Support\Priority;

#[Priority(Priority::FRAMEWORK - 29)]
final readonly class MatchRouteMiddleware implements HttpMiddleware
{
    public function __construct(
        private RouteMatcher $routeMatcher,
        private Container $container,
    ) {}

    public function __invoke(Request $request, HttpMiddlewareCallable $next): Response
    {
        // In long-running contexts, a previous request may have left a matched route behind.
        // It must be cleared before matching, since we only rebind it on a successful match.
        $this->container->unregister(MatchedRoute::class);

        $matchedRoute = $this->routeMatcher->match($request);

        if (! $matchedRoute instanceof MatchedRoute && $request->method === Method::HEAD && $request instanceof GenericRequest) {
            $matchedRoute = $this->routeMatcher->match($request->withMethod(Method::GET));
        }

        if (! $matchedRoute instanceof MatchedRoute) {
            return new NotFound();
        }

        // We register the matched route in the container, some internal framework components will need it.
        // It is scoped, so it does not survive into the next request in a long-running context.
        $this->container->scoped(MatchedRoute::class, $matchedRoute);

        return $next($request);
    }
}
