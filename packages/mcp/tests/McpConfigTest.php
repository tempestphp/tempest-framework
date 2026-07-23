<?php

declare(strict_types=1);

namespace Tempest\Mcp\Tests;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Mcp\Exceptions\McpServerWasAlreadyRegistered;
use Tempest\Mcp\Exceptions\McpServerWasNotFound;
use Tempest\Mcp\Exceptions\PrimitiveWasAlreadyRegistered;
use Tempest\Mcp\Exceptions\ResourceTemplateWasInvalid;
use Tempest\Mcp\McpConfig;
use Tempest\Mcp\McpResource;
use Tempest\Mcp\McpServer;
use Tempest\Mcp\McpTool;
use Tempest\Mcp\Tests\Fixtures\ExtendingFixtureServer;
use Tempest\Mcp\Tests\Fixtures\RegistryFixtureServer;
use Tempest\Mcp\Tests\Fixtures\SharedToolsFixture;
use Tempest\Mcp\Tests\Fixtures\SomeService;
use Tempest\Reflection\MethodReflector;

/**
 * @internal
 */
final class McpConfigTest extends TestCase
{
    #[Test]
    public function derives_server_defaults(): void
    {
        $config = new McpConfig();
        $config->addServer(RegistryFixtureServer::class, new McpServer());

        $server = $config->servers[RegistryFixtureServer::class];

        $this->assertSame('registry-fixture-server', $server->name);
        $this->assertSame('0.0.1', $server->version);
        $this->assertNull($server->path);
    }

    #[Test]
    public function normalizes_paths(): void
    {
        $config = new McpConfig();
        $config->addServer(RegistryFixtureServer::class, new McpServer(path: 'mcp/fixture/'));

        $this->assertSame('/mcp/fixture', $config->servers[RegistryFixtureServer::class]->path);
        $this->assertSame(RegistryFixtureServer::class, $config->getServerByPath('/mcp/fixture')?->class);
        $this->assertSame(RegistryFixtureServer::class, $config->getServerByPath('/mcp/fixture/')?->class);
        $this->assertSame(RegistryFixtureServer::class, $config->getServerByPath('mcp/fixture')?->class);
    }

    #[Test]
    public function rejects_duplicate_server_names(): void
    {
        $config = new McpConfig();
        $config->addServer(RegistryFixtureServer::class, new McpServer(name: 'duplicate'));

        $this->expectException(McpServerWasAlreadyRegistered::class);

        $config->addServer(SomeService::class, new McpServer(name: 'duplicate'));
    }

    #[Test]
    public function rejects_duplicate_server_paths(): void
    {
        $config = new McpConfig();
        $config->addServer(RegistryFixtureServer::class, new McpServer(name: 'a', path: '/mcp'));

        $this->expectException(McpServerWasAlreadyRegistered::class);

        $config->addServer(SomeService::class, new McpServer(name: 'b', path: '/mcp'));
    }

    #[Test]
    public function derives_tool_names_from_method_names(): void
    {
        $config = new McpConfig();
        $config->addServer(RegistryFixtureServer::class, new McpServer());
        $config->addTool(MethodReflector::fromParts(RegistryFixtureServer::class, 'multiWord'), new McpTool());

        $this->assertArrayHasKey('multi_word', $config->servers[RegistryFixtureServer::class]->tools);
    }

    #[Test]
    public function rejects_duplicate_tool_names(): void
    {
        $config = new McpConfig();
        $config->addServer(RegistryFixtureServer::class, new McpServer());
        $config->addTool(MethodReflector::fromParts(RegistryFixtureServer::class, 'multiWord'), new McpTool());

        $this->expectException(PrimitiveWasAlreadyRegistered::class);

        $config->addTool(MethodReflector::fromParts(RegistryFixtureServer::class, 'withoutParameters'), new McpTool(name: 'multi_word'));
    }

    #[Test]
    public function rejects_unknown_servers(): void
    {
        $config = new McpConfig();

        $this->expectException(McpServerWasNotFound::class);

        $config->addTool(
            MethodReflector::fromParts(RegistryFixtureServer::class, 'multiWord'),
            new McpTool(server: SomeService::class),
        );
    }

    #[Test]
    public function rejects_unattached_primitives(): void
    {
        $config = new McpConfig();

        $this->expectException(McpServerWasNotFound::class);

        $config->addTool(MethodReflector::fromParts(RegistryFixtureServer::class, 'multiWord'), new McpTool());
    }

    #[Test]
    public function attaches_inherited_methods_to_the_extending_server(): void
    {
        $config = new McpConfig();
        $config->addServer(ExtendingFixtureServer::class, new McpServer());
        $config->addTool(
            MethodReflector::fromParts(ExtendingFixtureServer::class, 'sharedTool'),
            new McpTool(),
            owner: ExtendingFixtureServer::class,
        );

        $tool = $config->servers[ExtendingFixtureServer::class]->tools['shared_tool'];

        $this->assertSame(ExtendingFixtureServer::class, $tool->class);
    }

    #[Test]
    public function skips_primitives_inherited_by_non_server_classes(): void
    {
        $config = new McpConfig();
        $config->addServer(RegistryFixtureServer::class, new McpServer());
        $config->addTool(
            MethodReflector::fromParts(ExtendingFixtureServer::class, 'sharedTool'),
            new McpTool(),
            owner: ExtendingFixtureServer::class,
        );

        $this->assertSame([], $config->servers[RegistryFixtureServer::class]->tools);
    }

    #[Test]
    public function skips_unattached_primitives_on_abstract_classes(): void
    {
        $config = new McpConfig();
        $config->addTool(
            MethodReflector::fromParts(SharedToolsFixture::class, 'sharedTool'),
            new McpTool(),
            owner: SharedToolsFixture::class,
        );

        $this->assertSame([], $config->servers);
    }

    #[Test]
    public function registers_explicitly_attached_primitives_only_from_their_declaring_class(): void
    {
        $config = new McpConfig();
        $config->addServer(RegistryFixtureServer::class, new McpServer());

        $attribute = new McpTool(server: RegistryFixtureServer::class);

        $config->addTool(MethodReflector::fromParts(ExtendingFixtureServer::class, 'sharedTool'), $attribute, owner: ExtendingFixtureServer::class);
        $config->addTool(MethodReflector::fromParts(SharedToolsFixture::class, 'sharedTool'), $attribute, owner: SharedToolsFixture::class);

        $this->assertArrayHasKey('shared_tool', $config->servers[RegistryFixtureServer::class]->tools);
    }

    #[Test]
    public function rejects_template_variables_without_matching_parameters(): void
    {
        $config = new McpConfig();
        $config->addServer(RegistryFixtureServer::class, new McpServer());

        $this->expectException(ResourceTemplateWasInvalid::class);

        $config->addResource(
            MethodReflector::fromParts(RegistryFixtureServer::class, 'withoutParameters'),
            new McpResource(uri: 'fixture://things/{thing}'),
        );
    }

    #[Test]
    public function separates_resources_from_resource_templates(): void
    {
        $config = new McpConfig();
        $config->addServer(RegistryFixtureServer::class, new McpServer());
        $config->addResource(MethodReflector::fromParts(RegistryFixtureServer::class, 'item'), new McpResource(uri: 'fixture://items/{id}'));
        $config->addResource(MethodReflector::fromParts(RegistryFixtureServer::class, 'withoutParameters'), new McpResource(uri: 'fixture://static'));

        $server = $config->servers[RegistryFixtureServer::class];

        $this->assertArrayHasKey('fixture://items/{id}', $server->resourceTemplates);
        $this->assertArrayHasKey('fixture://static', $server->resources);
        $this->assertArrayNotHasKey('fixture://items/{id}', $server->resources);
    }
}
