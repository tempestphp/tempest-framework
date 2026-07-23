<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Mcp;

use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Attributes\Test;
use Tempest\Mcp\Exceptions\McpServerWasNotFound;
use Tests\Tempest\Fixtures\Mcp\DemoMcpServer;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class McpTesterTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function connects_by_class_or_name(): void
    {
        $this->mcp->onServer(DemoMcpServer::class)->send('ping')->assertOk();
        $this->mcp->onServer('demo-mcp-server')->send('ping')->assertOk();
    }

    #[Test]
    public function fails_for_unknown_servers(): void
    {
        $this->expectException(McpServerWasNotFound::class);
        $this->expectExceptionMessage('There is no MCP server named `unknown-server`.');

        $this->mcp->onServer('unknown-server');
    }

    #[Test]
    public function fails_for_classes_without_the_server_attribute(): void
    {
        $this->expectException(McpServerWasNotFound::class);
        $this->expectExceptionMessage('The class `' . self::class . '` is not annotated with `#[McpServer]`.');

        $this->mcp->onServer(self::class);
    }

    #[Test]
    public function assertions_detect_content(): void
    {
        $this->mcp
            ->onServer(DemoMcpServer::class)
            ->callTool('greet', ['name' => 'Brent'])
            ->assertOk()
            ->assertText('Hello, Brent!')
            ->assertTextContains('Brent')
            ->assertSee('Hello');
    }

    #[Test]
    public function assert_ok_fails_on_tool_errors(): void
    {
        $response = $this->mcp
            ->onServer(DemoMcpServer::class)
            ->callTool('fail');

        $this->expectException(AssertionFailedError::class);

        $response->assertOk();
    }

    #[Test]
    public function assert_error_fails_on_successful_calls(): void
    {
        $response = $this->mcp
            ->onServer(DemoMcpServer::class)
            ->callTool('add', ['a' => 1, 'b' => 2]);

        $this->expectException(AssertionFailedError::class);

        $response->assertError();
    }
}
