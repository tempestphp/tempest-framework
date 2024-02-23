<?php

declare(strict_types=1);

namespace Tempest\Console\Commands;

use Tempest\Console\Console;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\ConsoleStyle;
use Tempest\Http\Route;
use Tempest\Http\Router;

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

        /**
         * Here we flatten the multidimensional array and then run
         * this array through a custom sort function that sorts
         * first by URI and then by method name.
         *
         * @var \Tempest\Http\Route[] $routes
         */
        $routes = array_merge([], ...array_values($this->router->getRoutes()));

        usort($routes, function (Route $a, Route $b) {
            if ($a->uri !== $b->uri) {
                return $a->uri < $b->uri ? -1 : 1;
            }

            if ($a->method === $b->method) {
                return 0;
            }

            return $a->method->name < $b->method->name ? -1 : 1;
        });

        foreach ($routes as $route) {
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
