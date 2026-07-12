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
        $matchedRoute = $this->routeMatcher->match($request);

        if (! $matchedRoute instanceof MatchedRoute && $request->method === Method::HEAD && $request instanceof GenericRequest) {
            $matchedRoute = $this->routeMatcher->match($request->withMethod(Method::GET));
        }

        if (! $matchedRoute instanceof MatchedRoute) {
            return new NotFound();
        }

        // We register the matched route in the container, some internal framework components will need it
        $this->container->singleton(MatchedRoute::class, fn () => $matchedRoute);

        return $next($request);
    }
}
