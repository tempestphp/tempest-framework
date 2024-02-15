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

    #[ConsoleCommand('route:list')]
    public function list(): void
    {
        $sortedRoutes = [];

        foreach($this->router->getRoutes() as $method => $routesForMethod) {
            foreach ($routesForMethod as $uri => $route) {
                $sortedRoutes[$uri] = [
                    'method' => $method,
                    'uri' => $uri,
                    'controller' => $route[0],
                    'action' => $route[1],
                ];
            }
        }

        ksort($sortedRoutes);

        foreach ($sortedRoutes as ['method' => $method, 'uri' => $uri, 'controller' => $controller, 'action' => $action]) {
            $this->console->writeln(implode(' ', [
                ConsoleStyle::FG_BLUE(str_pad($method, 4)),
                ConsoleStyle::FG_DARK_BLUE($uri),
                PHP_EOL,
                '   ',
                $controller . '::' . $action . '()',
            ]));
        }
    }
}
