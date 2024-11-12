<?php

declare(strict_types=1);

namespace Tempest\Http\Routing\Matching;

use Psr\Http\Message\ServerRequestInterface as PsrRequest;
use Tempest\Http\MatchedRoute;
use Tempest\Http\Route;
use Tempest\Http\RouteConfig;
use Tempest\Http\Routing\Construction\MarkedRoute;

final readonly class GenericRouteMatcher implements RouteMatcher
{
    public function __construct(private RouteConfig $routeConfig)
    {
    }

    public function match(PsrRequest $request): ?MatchedRoute
    {
        // Try to match routes without any parameters
        if (($staticRoute = $this->matchStaticRoute($request)) !== null) {
            return $staticRoute;
        }

        // match dynamic routes
        return $this->matchDynamicRoute($request);
    }

    private function matchStaticRoute(PsrRequest $request): ?MatchedRoute
    {
        $staticRoute = $this->routeConfig->staticRoutes[$request->getMethod()][$request->getUri()->getPath()] ?? null;

        if ($staticRoute === null) {
            return null;
        }

        return new MatchedRoute($staticRoute, []);
    }

    private function matchDynamicRoute(PsrRequest $request): ?MatchedRoute
    {
        // If there are no routes for the given request method, we immediately stop
        $routesForMethod = $this->routeConfig->dynamicRoutes[$request->getMethod()] ?? null;
        if ($routesForMethod === null) {
            return null;
        }

        // Get matching regex for route
        $matchingRegexForMethod = $this->routeConfig->matchingRegexes[$request->getMethod()];

        // Then we'll use this regex to see whether we have a match or not
        $matchResult = preg_match($matchingRegexForMethod, $request->getUri()->getPath(), $routingMatches);

        if (! $matchResult || ! array_key_exists(MarkedRoute::REGEX_MARK_TOKEN, $routingMatches)) {
            return null;
        }

        // Get the route based on the matched mark
        $route = $routesForMethod[$routingMatches[MarkedRoute::REGEX_MARK_TOKEN]];

        // Extract the parameters based on the route and matches
        $routeParams = $this->extractParams($route, $routingMatches);

        return new MatchedRoute($route, $routeParams);
    }

    /**
     * Extracts route parameters from the routeMatches
     *
     * @param array<string|int, string> $routeMatches
     * @return array<string, string>
     */
    private function extractParams(Route $route, array $routeMatches): array
    {
        $valueMap = [];
        foreach ($route->params as $i => $param) {
            $valueMap[$param] = $routeMatches[$i + 1];
        }

        return $valueMap;
    }
}
