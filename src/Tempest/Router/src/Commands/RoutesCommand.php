<?php

declare(strict_types=1);

namespace Tempest\Router\Commands;

use Tempest\Console\Console;
use Tempest\Console\ConsoleArgument;
use Tempest\Console\ConsoleCommand;
use Tempest\Http\Method;
use Tempest\Reflection\MethodReflector;
use Tempest\Router\RouteConfig;
use Tempest\Router\Routing\Construction\DiscoveredRoute;

use function Tempest\Support\str;
use function Tempest\Support\Str\after_last;
use function Tempest\Support\Str\before_last;

final readonly class RoutesCommand
{
    public function __construct(
        private Console $console,
        private RouteConfig $routeConfig,
    ) {}

    #[ConsoleCommand(name: 'routes', description: 'Lists all registered routes', aliases: ['routes:list', 'list:routes', 'route:list'])]
    public function list(
        #[ConsoleArgument(description: 'Outputs registered routes as JSON')]
        bool $json = false,
    ): void {
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

        if ($json) {
            $this->console->writeRaw(json_encode($sortedRoutes, flags: JSON_UNESCAPED_UNICODE));

            return;
        }

        $this->console->header('Registered routes', subheader: 'These routes are registered in your application.');
        $this->console->writeln();

        /** @var DiscoveredRoute $route */
        foreach ($sortedRoutes as $route) {
            $color = match ($route->method) {
                Method::GET => 'magenta',
                Method::POST => 'yellow',
                Method::PUT => 'green',
                Method::PATCH => 'blue',
                Method::DELETE => 'red',
                default => 'gray',
            };

            $this->console->keyValue(
                key: str($route->method->value)
                    ->alignRight(width: 8)
                    ->wrap("<style='fg-{$color}'>", '</style>')
                    ->append(' ', $this->formatRouteUri($route->uri))
                    ->toString(),
                value: $this->formatRouteHandler($route->handler),
                useAvailableWidth: true,
            );
        }
    }

    private function formatRouteUri(string $uri): string
    {
        return str($uri)
            ->replaceRegex('/{.*?}/', '<style="fg-blue">$0</style>')
            ->toString();
    }

    private function formatRouteHandler(MethodReflector $handler): string
    {
        $namespace = before_last($handler->getDeclaringClass()->getName(), '\\');
        $name = after_last($handler->getDeclaringClass()->getName(), '\\');
        $method = $handler->getName();

        return sprintf(
            "<style='fg-white dim'>%s\\</style><style='fg-white'>%s</style><style='dim'>::</style><style='fg-white'>%s</style><style='dim'>()</style>",
            $namespace,
            $name,
            $method,
        );
    }
}
