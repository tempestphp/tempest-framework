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

    /**
     * __sleep is called before serialize and returns the public properties to serialize. We do not want the routingTree
     * to be serialized, but we do want the result to be serialized. Thus prepareMatchingRegexes is called and the
     * resulting matchingRegexes are stored.
     */
    public function __sleep(): array
    {
        $this->prepareMatchingRegexes();

        return ['staticRoutes', 'dynamicRoutes', 'matchingRegexes'];
    }

    /**
     * __wakeup is called after unserialize. We do not serialize the routingTree thus we need to provide some default
     *  for it. Otherwise, it will be uninitialized and cause issues when tempest expects it to be defined.
     */
    public function __wakeup(): void
    {
        $this->routingTree = new RoutingTree();
    }
}
