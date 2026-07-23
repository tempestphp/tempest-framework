<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Mcp;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Console\ExitCode;
use Tempest\Mcp\McpConfig;
use Tempest\Mcp\StdioTransport;
use Tests\Tempest\Fixtures\Mcp\StdioMcpServer;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\Support\Json\decode;
use function Tempest\Support\Json\encode;

/**
 * @internal
 */
final class McpStdioTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function runs_a_message_loop_over_streams(): void
    {
        $input = fopen('php://memory', 'r+');
        $output = fopen('php://memory', 'r+');

        fwrite($input, encode(['jsonrpc' => '2.0', 'id' => 1, 'method' => 'initialize', 'params' => ['protocolVersion' => '2025-11-25']]) . PHP_EOL);
        fwrite($input, encode(['jsonrpc' => '2.0', 'method' => 'notifications/initialized']) . PHP_EOL);
        fwrite($input, encode(['jsonrpc' => '2.0', 'id' => 2, 'method' => 'tools/call', 'params' => ['name' => 'pong']]) . PHP_EOL);
        rewind($input);

        $server = $this->container->get(McpConfig::class)->servers[StdioMcpServer::class];

        $this->container->get(StdioTransport::class)->run($server, $input, $output);

        rewind($output);

        $responses = array_values(array_filter(explode(PHP_EOL, stream_get_contents($output))));

        $this->assertCount(2, $responses);

        $initialize = decode($responses[0]);

        $this->assertSame(1, $initialize['id']);
        $this->assertSame('stdio-demo', $initialize['result']['serverInfo']['name']);

        $call = decode($responses[1]);

        $this->assertSame(2, $call['id']);
        $this->assertSame('pong', $call['result']['content'][0]['text']);
    }

    #[Test]
    public function serve_command_fails_for_unknown_servers(): void
    {
        $this->console
            ->call('mcp:serve', ['server' => 'nope'])
            ->assertExitCode(ExitCode::INVALID)
            ->assertContains('There is no MCP server named `nope`');
    }

    #[Test]
    public function list_command_shows_discovered_servers(): void
    {
        $this->console
            ->call('mcp:list')
            ->assertSuccess()
            ->assertContains('demo-mcp-server')
            ->assertContains('stdio-demo')
            ->assertContains('http (/mcp/demo)');
    }
}
