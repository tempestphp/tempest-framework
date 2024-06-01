<?php

declare(strict_types=1);

namespace Tempest\Console\Commands;

use Tempest\Console\Console;
use Tempest\Console\ConsoleCommand;
use Tempest\Http\RouteConfig;

final readonly class RoutesCommand
{
    public function __construct(
        private Console $console,
        private RouteConfig $routeConfig,
    ) {
    }

    #[ConsoleCommand(
        name: 'routes',
        description: 'List all registered routes'
    )]
    public function list(): void
    {
        $sortedRoutes = [];

        foreach ($this->routeConfig->dynamicRoutes as $method => $routesForMethod) {
            foreach ($routesForMethod as $route) {
                $sortedRoutes["{$route->uri}:{$method}"] = $route;
            }
        }

        foreach ($this->routeConfig->staticRoutes as $method => $routesForMethod) {
            foreach ($routesForMethod as $route) {
                $sortedRoutes["{$route->uri}:{$method}"] = $route;
            }
        }

        ksort($sortedRoutes);

        foreach ($sortedRoutes as $route) {
            $this->console->writeln(implode(' ', [
                '<strong>' . str_pad($route->method->value, 4) . '</strong>',
                '<em>' . $route->uri . '</em>',
                PHP_EOL,
                '   ',
                $route->handler->getDeclaringClass()->getName() . '::' . $route->handler->getName() . '()',
            ]));
        }
    }
}
