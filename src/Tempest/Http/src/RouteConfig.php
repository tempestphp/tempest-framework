<?php

declare(strict_types=1);

namespace Tempest\Http;

use Tempest\Http\Routing\Construction\MarkedRoute;
use Tempest\Http\Routing\Construction\RoutingTree;
use Tempest\Reflection\MethodReflector;

final class RouteConfig
{
    /** @var string The mark to give the next route in the matching Regex */
    private string $regexMark = 'a';

    /** @var array<string, string> */
    public array $matchingRegexes = [];

    public RoutingTree $routingTree;

    public function __construct(
        /** @var array<string, array<string, \Tempest\Http\Route>> */
        public array $staticRoutes = [],
        /** @var array<string, array<string, \Tempest\Http\Route>> */
        public array $dynamicRoutes = [],
    ) {
        $this->routingTree = new RoutingTree();
    }

    public function addRoute(MethodReflector $handler, Route $route): self
    {
        $route->setHandler($handler);

        if ($route->isDynamic) {
            $this->regexMark = str_increment($this->regexMark);
            $this->dynamicRoutes[$route->method->value][$this->regexMark] = $route;

            $this->routingTree->add(
                new MarkedRoute(
                    mark: $this->regexMark,
                    route: $route,
                )
            );

        } else {
            $uriWithTrailingSlash = rtrim($route->uri, '/');

            $this->staticRoutes[$route->method->value][$uriWithTrailingSlash] = $route;
            $this->staticRoutes[$route->method->value][$uriWithTrailingSlash . '/'] = $route;
        }

        return $this;
    }

    public function prepareMatchingRegexes(): void
    {
        if (! empty($this->matchingRegexes)) {
            return;
        }

        $this->matchingRegexes = $this->routingTree->toMatchingRegexes();
    }

    public function __sleep(): array
    {
        $this->prepareMatchingRegexes();

        return ['staticRoutes', 'dynamicRoutes', 'matchingRegexes'];
    }

    public function __wakeup(): void
    {
        $this->routingTree = new RoutingTree();
    }
}
