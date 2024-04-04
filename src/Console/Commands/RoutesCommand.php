<?php

declare(strict_types=1);

namespace Tempest\Console\Commands;

use Tempest\Console\Console;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\ConsoleOutputBuilder;
use Tempest\Console\ConsoleStyle;
use Tempest\Http\RouteConfig;

final readonly class RoutesCommand
{
    public function __construct(
        private Console $console,
        private RouteConfig $routeConfig,
        private ConsoleOutputBuilder $builder,
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


        $this->builder
            ->header("Tempest")
            ->warning('Registered routes')
            ->when(empty($sortedRoutes), fn (ConsoleOutputBuilder $builder) => $builder->info('No routes registered'))
            ->when(! empty($sortedRoutes), function (ConsoleOutputBuilder $builder) use ($sortedRoutes) {
                foreach ($sortedRoutes as $route) {
                    $builder->raw(
                        implode(' ', [
                            ConsoleStyle::FG_BLUE(str_pad($route->method->value, 4)),
                            ConsoleStyle::FG_DARK_BLUE($route->uri),
                            PHP_EOL,
                            '   ',
                            $route->handler->getDeclaringClass()->getName() . '::' . $route->handler->getName() . '()',
                        ])
                    );
                }
            })
            ->write($this->console);
    }
}
