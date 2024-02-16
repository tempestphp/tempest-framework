<?php

declare(strict_types=1);

namespace Tempest\Console\Commands;

use Tempest\Console\ConsoleCommand;
use Tempest\Console\ConsoleStyle;
use Tempest\Interface\Console;
use Tempest\Interface\Router;

final readonly class RoutesCommand
{
    public function __construct(
        private Console $console,
        private Router $router,
    ) {
    }

    #[ConsoleCommand(
        name: 'routes',
        description: 'List all registered routes'
    )]
    public function list(): void
    {
        /** @var \Tempest\Http\Route[] $sortedRoutes */
        $sortedRoutes = [];

        foreach($this->router->getRoutes() as $routesForMethod) {
            foreach ($routesForMethod as $uri => $route) {
                $sortedRoutes[$uri] = $route;
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
