<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Mcp;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Mcp\McpConfig;
use Tests\Tempest\Fixtures\Mcp\AbstractMcpTools;
use Tests\Tempest\Fixtures\Mcp\BaseMcpServer;
use Tests\Tempest\Fixtures\Mcp\DemoMcpServer;
use Tests\Tempest\Fixtures\Mcp\ExtendedMcpServer;
use Tests\Tempest\Fixtures\Mcp\InheritedMcpServer;
use Tests\Tempest\Fixtures\Mcp\StdioMcpServer;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class McpDiscoveryTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function discovers_servers(): void
    {
        $config = $this->container->get(McpConfig::class);

        $demo = $config->servers[DemoMcpServer::class];

        $this->assertSame('demo-mcp-server', $demo->name);
        $this->assertSame('1.2.3', $demo->version);
        $this->assertSame('A demo MCP server for testing.', $demo->instructions);
        $this->assertSame('/mcp/demo', $demo->path);

        $stdio = $config->servers[StdioMcpServer::class];

        $this->assertSame('stdio-demo', $stdio->name);
        $this->assertSame('0.0.1', $stdio->version);
        $this->assertNull($stdio->instructions);
        $this->assertNull($stdio->path);
    }

    #[Test]
    public function discovers_primitives_on_server_classes(): void
    {
        $config = $this->container->get(McpConfig::class);

        $demo = $config->servers[DemoMcpServer::class];

        $this->assertArrayHasKey('add', $demo->tools);
        $this->assertArrayHasKey('greet', $demo->tools);
        $this->assertArrayHasKey('rate', $demo->tools);
        $this->assertSame('Adds two numbers', $demo->tools['add']->description);

        $this->assertArrayHasKey('review_code', $demo->prompts);
        $this->assertSame('Generates a code review prompt', $demo->prompts['review_code']->description);

        $this->assertArrayHasKey('demo://config', $demo->resources);
        $this->assertArrayHasKey('demo://logo', $demo->resources);
        $this->assertArrayHasKey('demo://users/{id}', $demo->resourceTemplates);
        $this->assertSame('application/json', $demo->resources['demo://config']->mimeType);
        $this->assertSame('user', $demo->resourceTemplates['demo://users/{id}']->name);
    }

    #[Test]
    public function discovers_external_primitives(): void
    {
        $config = $this->container->get(McpConfig::class);

        $demo = $config->servers[DemoMcpServer::class];

        $this->assertArrayHasKey('repeat', $demo->tools);
        $this->assertSame('Repeats a message', $demo->tools['repeat']->description);
    }

    #[Test]
    public function servers_inherit_primitives_from_parent_classes(): void
    {
        $config = $this->container->get(McpConfig::class);

        $base = $config->servers[BaseMcpServer::class];

        $this->assertEqualsCanonicalizing(['base_tool', 'shared'], array_keys($base->tools));
        $this->assertSame(BaseMcpServer::class, $base->tools['shared']->class);

        $inherited = $config->servers[InheritedMcpServer::class];

        $this->assertEqualsCanonicalizing(['base_tool', 'own_tool', 'shared'], array_keys($inherited->tools));
        $this->assertSame(InheritedMcpServer::class, $inherited->tools['base_tool']->class);
    }

    #[Test]
    public function subclasses_without_the_server_attribute_are_not_servers(): void
    {
        $config = $this->container->get(McpConfig::class);

        $this->assertArrayNotHasKey(ExtendedMcpServer::class, $config->servers);
        $this->assertArrayNotHasKey(AbstractMcpTools::class, $config->servers);
    }

    #[Test]
    public function servers_are_resolvable_by_name_and_path(): void
    {
        $config = $this->container->get(McpConfig::class);

        $this->assertSame(DemoMcpServer::class, $config->getServerByName('demo-mcp-server')?->class);
        $this->assertSame(DemoMcpServer::class, $config->getServerByPath('/mcp/demo')?->class);
        $this->assertSame(StdioMcpServer::class, $config->getServerByName('stdio-demo')?->class);
        $this->assertNull($config->getServerByName('unknown'));
        $this->assertNull($config->getServerByPath('/unknown'));
    }
}
