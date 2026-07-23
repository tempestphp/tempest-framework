<?php

declare(strict_types=1);

namespace Tempest\Mcp\Tests\Fixtures;

use Tempest\Mcp\McpResource;
use Tempest\Mcp\McpServer;
use Tempest\Mcp\McpTool;

#[McpServer]
final class RegistryFixtureServer
{
    #[McpTool]
    public function multiWord(): string
    {
        return 'multi';
    }

    #[McpResource(uri: 'fixture://items/{id}')]
    public function item(int $id): string
    {
        return "Item #{$id}";
    }

    public function withoutParameters(): string
    {
        return 'nothing';
    }
}
