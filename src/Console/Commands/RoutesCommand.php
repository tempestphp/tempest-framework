<?php

declare(strict_types=1);

namespace Tempest\Console\Commands;

use Tempest\Console\Console;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\ConsoleStyle;
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

        foreach ($this->routeConfig->routes as $method => $routesForMethod) {
            foreach ($routesForMethod as $route) {
                $sortedRoutes["{$route->uri}:{$method}"] = $route;
            }
        }

        ksort($sortedRoutes);

        foreach ($sortedRoutes as $route) {
            $this->console->writeln(implode(' ', [
                ConsoleStyle::FG_BLUE(str_pad($route->method->value, 4)),
                ConsoleStyle::FG_DARK_BLUE($route->uri),
                PHP_EOL,
                '   ',
                $route->handler->getDeclaringClass()->getName() . '::' . $route->handler->getName() . '()',
            ]));
        }
    }
}
