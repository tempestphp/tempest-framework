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
    public function socket_timeouts_do_not_stop_the_message_loop(): void
    {
        if (PHP_OS_FAMILY === 'Windows') {
            self::markTestSkipped('Unix socket pairs are not available on Windows.');
        }

        $sockets = stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, STREAM_IPPROTO_IP);

        if ($sockets === false) {
            self::fail('Could not create a socket pair.');
        }

        [$input, $writer] = $sockets;
        $output = fopen('php://memory', 'r+');
        $message = encode(['jsonrpc' => '2.0', 'id' => 1, 'method' => 'initialize', 'params' => ['protocolVersion' => '2025-11-25']]) . PHP_EOL;
        $pipes = [];
        $process = proc_open(
            [PHP_BINARY, '-r', 'usleep(100_000); echo $argv[1];', $message],
            [
                0 => ['pipe', 'r'],
                1 => $writer,
                2 => ['pipe', 'w'],
            ],
            $pipes,
        );

        if (! is_resource($process)) {
            self::fail('Could not start the delayed writer process.');
        }

        fclose($writer);
        foreach ($pipes as $pipe) {
            if (! is_resource($pipe)) {
                continue;
            }

            fclose($pipe);
        }

        stream_set_timeout($input, seconds: 0, microseconds: 25_000);

        $server = $this->container->get(McpConfig::class)->servers[StdioMcpServer::class];

        $this->container->get(StdioTransport::class)->run($server, $input, $output);

        $this->assertSame(0, proc_close($process));

        rewind($output);

        $contents = stream_get_contents($output);

        if ($contents === false) {
            self::fail('Could not read the transport output.');
        }

        $this->assertStringContainsString('"id":1', $contents);
        $this->assertStringContainsString('"name":"stdio-demo"', $contents);
    }

    #[Test]
    public function windows_stdio_polling_waits_for_delayed_messages(): void
    {
        if (PHP_OS_FAMILY !== 'Windows') {
            self::markTestSkipped('Windows-specific stdio polling test.');
        }

        $output = fopen('php://memory', 'r+');
        $message = encode(['jsonrpc' => '2.0', 'id' => 1, 'method' => 'initialize', 'params' => ['protocolVersion' => '2025-11-25']]) . PHP_EOL;
        $pipes = [];
        $process = proc_open(
            [PHP_BINARY, '-r', 'usleep(100_000); echo $argv[1];', $message],
            [
                0 => ['pipe', 'r'],
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ],
            $pipes,
        );

        if (! is_resource($process)) {
            self::fail('Could not start the delayed writer process.');
        }

        $stdin = $pipes[0] ?? null;
        $input = $pipes[1] ?? null;
        $stderr = $pipes[2] ?? null;

        if (! is_resource($stdin) || ! is_resource($input) || ! is_resource($stderr)) {
            self::fail('Could not open pipes for the delayed writer process.');
        }

        fclose($stdin);
        fclose($stderr);

        $server = $this->container->get(McpConfig::class)->servers[StdioMcpServer::class];

        $this->container->get(StdioTransport::class)->run($server, $input, $output);

        fclose($input);

        $this->assertSame(0, proc_close($process));

        rewind($output);

        $contents = stream_get_contents($output);

        if ($contents === false) {
            self::fail('Could not read the transport output.');
        }

        $this->assertStringContainsString('"id":1', $contents);
        $this->assertStringContainsString('"name":"stdio-demo"', $contents);
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
