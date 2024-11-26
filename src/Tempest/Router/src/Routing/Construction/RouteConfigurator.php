<?php

declare(strict_types=1);

namespace Tempest\Router\Routing\Construction;

use Tempest\Container\Singleton;
use Tempest\Router\Route;
use Tempest\Router\RouteConfig;

/**
 * @internal
 */
#[Singleton]
final class RouteConfigurator
{
    /** @var string The mark to give the next route in the matching Regex */
    private string $regexMark = 'a';

    private array $staticRoutes = [];

    private array $dynamicRoutes = [];

    private bool $isDirty = false;

    private RoutingTree $routingTree;

    public function __construct()
    {
        $this->routingTree = new RoutingTree();
    }

    public function addRoute(Route $route): void
    {
        $this->isDirty = true;

        if ($route->isDynamic) {
            $this->addDynamicRoute($route);
        } else {
            $this->addStaticRoute($route);
        }
    }

    private function addDynamicRoute(Route $route): void
    {
        $markedRoute = $this->markRoute($route);
        $this->dynamicRoutes[$route->method->value][$markedRoute->mark] = $route;

        $this->routingTree->add($markedRoute);
    }

    private function addStaticRoute(Route $route): void
    {
        $uriWithTrailingSlash = rtrim($route->uri, '/');

        $this->staticRoutes[$route->method->value][$uriWithTrailingSlash] = $route;
        $this->staticRoutes[$route->method->value][$uriWithTrailingSlash . '/'] = $route;
    }

    private function markRoute(Route $route): MarkedRoute
    {
        $this->regexMark = str_increment($this->regexMark);

        return new MarkedRoute(
            mark: $this->regexMark,
            route: $route,
        );
    }

    public function toRouteConfig(): RouteConfig
    {
        $this->isDirty = false;

        return new RouteConfig(
            $this->staticRoutes,
            $this->dynamicRoutes,
            $this->routingTree->toMatchingRegexes(),
        );
    }

    public function isDirty(): bool
    {
        return $this->isDirty;
    }
}
