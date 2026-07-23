<?php

declare(strict_types=1);

namespace Tempest\Mcp;

use Tempest\Discovery\Discovery;
use Tempest\Discovery\DiscoveryLocation;
use Tempest\Discovery\IsDiscovery;
use Tempest\Reflection\ClassReflector;
use Tempest\Reflection\MethodReflector;
use Tempest\Router\Delete;
use Tempest\Router\Get;
use Tempest\Router\Post;
use Tempest\Router\PreventCrossSiteRequestsMiddleware;
use Tempest\Router\RouteConfig;
use Tempest\Router\Routing\Construction\DiscoveredRoute;
use Tempest\Router\Routing\Construction\RouteConfigurator;
use Tempest\Router\WithoutMiddleware;

final class McpDiscovery implements Discovery
{
    use IsDiscovery;

    public function __construct(
        private readonly McpConfig $mcpConfig,
        private readonly SchemaGenerator $schemaGenerator,
        private readonly RouteConfigurator $routeConfigurator,
        private readonly RouteConfig $routeConfig,
    ) {}

    public function discover(DiscoveryLocation $location, ClassReflector $class): void
    {
        if (($server = $class->getAttribute(McpServer::class)) instanceof McpServer) {
            $this->discoveryItems->add($location, [$class->getName(), $server]);
        }

        foreach ($class->getPublicMethods() as $method) {
            foreach ([McpTool::class, McpPrompt::class, McpResource::class] as $attributeClass) {
                if (! ($attribute = $method->getAttribute($attributeClass))) {
                    continue;
                }

                $this->discoveryItems->add($location, [$class->getName(), $method, $attribute]);
            }
        }
    }

    public function apply(): void
    {
        $servers = [];
        $primitives = [];

        foreach ($this->discoveryItems as $item) {
            if ($item[1] instanceof McpServer) {
                $servers[] = $item;
            } else {
                $primitives[] = $item;
            }
        }

        foreach ($servers as [$class, $attribute]) {
            $this->mcpConfig->addServer($class, $attribute);
        }

        foreach ($primitives as [$owner, $method, $attribute]) {
            $this->schemaGenerator->assertSupported($method);

            match (true) {
                $attribute instanceof McpTool => $this->mcpConfig->addTool($method, $attribute, $owner),
                $attribute instanceof McpPrompt => $this->mcpConfig->addPrompt($method, $attribute, $owner),
                $attribute instanceof McpResource => $this->mcpConfig->addResource($method, $attribute, $owner),
                default => null,
            };
        }

        $this->registerRoutes();
    }

    private function registerRoutes(): void
    {
        foreach ($this->mcpConfig->servers as $server) {
            if ($server->path === null) {
                continue;
            }

            foreach ([new Post($server->path), new Get($server->path), new Delete($server->path)] as $route) {
                $this->routeConfigurator->addRoute(DiscoveredRoute::fromRoute(
                    $route,
                    [new WithoutMiddleware(PreventCrossSiteRequestsMiddleware::class)],
                    MethodReflector::fromParts(McpHttpController::class, '__invoke'),
                ));
            }
        }

        if ($this->routeConfigurator->isDirty()) {
            $this->routeConfig->apply($this->routeConfigurator->toRouteConfig());
        }
    }
}
