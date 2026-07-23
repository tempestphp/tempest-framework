<?php

declare(strict_types=1);

namespace Tempest\Mcp\Testing;

use Tempest\Container\Container;
use Tempest\Mcp\Exceptions\McpServerWasNotFound;
use Tempest\Mcp\McpConfig;
use Tempest\Mcp\McpRequestHandler;

final readonly class McpTester
{
    public function __construct(
        private Container $container,
    ) {}

    /**
     * Opens an in-process connection to the given MCP server, performing the initialization handshake.
     *
     * @param class-string|string $server The `#[McpServer]` class or server name to connect to.
     */
    public function onServer(string $server): McpTestConnection
    {
        $config = $this->container->get(McpConfig::class);

        $definition =
            $config->servers[$server] ?? $config->getServerByName($server) ?? throw class_exists($server)
                ? McpServerWasNotFound::withClass($server)
                : McpServerWasNotFound::withName($server);

        $connection = new McpTestConnection(
            requestHandler: $this->container->get(McpRequestHandler::class),
            server: $definition,
        );

        $connection->initialize();

        return $connection;
    }
}
