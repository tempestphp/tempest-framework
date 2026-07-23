<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Mcp;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Framework\Testing\Http\TestResponseHelper;
use Tempest\Http\Status;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\Support\Json\decode;
use function Tempest\Support\Json\encode;

/**
 * @internal
 */
final class McpHttpTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function lists_tools_over_http(): void
    {
        $response = $this->post(['jsonrpc' => '2.0', 'id' => 1, 'method' => 'tools/list']);

        $response->assertOk()->assertHeaderContains('Content-Type', 'application/json');

        $body = $this->body($response);

        $this->assertSame('2.0', $body['jsonrpc']);
        $this->assertSame(1, $body['id']);
        $this->assertContains('add', array_column($body['result']['tools'], 'name'));
    }

    #[Test]
    public function trailing_slashes_resolve_the_same_server(): void
    {
        $response = $this->http->post('/mcp/demo/', encode(['jsonrpc' => '2.0', 'id' => 1, 'method' => 'ping']), headers: ['Content-Type' => 'application/json']);

        $response->assertOk();

        $body = $this->body($response);

        $this->assertSame(1, $body['id']);
        $this->assertArrayHasKey('result', $body);
    }

    #[Test]
    public function non_post_requests_are_method_not_allowed(): void
    {
        $this->http->get('/mcp/demo')->assertStatus(Status::METHOD_NOT_ALLOWED);
        $this->http->delete('/mcp/demo')->assertStatus(Status::METHOD_NOT_ALLOWED);
    }

    #[Test]
    public function calls_a_tool_over_http(): void
    {
        $response = $this->post([
            'jsonrpc' => '2.0',
            'id' => 2,
            'method' => 'tools/call',
            'params' => [
                'name' => 'add',
                'arguments' => ['a' => 20, 'b' => 22],
            ],
        ]);

        $response->assertOk();

        $this->assertSame('42', $this->body($response)['result']['content'][0]['text']);
    }

    #[Test]
    public function notifications_are_accepted_without_content(): void
    {
        $this
            ->post(['jsonrpc' => '2.0', 'method' => 'notifications/initialized'])
            ->assertStatus(Status::ACCEPTED);
    }

    #[Test]
    public function invalid_json_is_a_parse_error(): void
    {
        $response = $this->http->post('/mcp/demo', 'not json', headers: ['Content-Type' => 'application/json']);

        $response->assertOk();

        $this->assertSame(-32_700, $this->body($response)['error']['code']);
    }

    #[Test]
    public function servers_without_a_path_have_no_route(): void
    {
        $this->http
            ->post('/mcp/stdio-demo', encode(['jsonrpc' => '2.0', 'id' => 1, 'method' => 'ping']))
            ->assertNotFound();
    }

    private function post(array $message): TestResponseHelper
    {
        return $this->http->post('/mcp/demo', encode($message), headers: ['Content-Type' => 'application/json']);
    }

    private function body(TestResponseHelper $response): array
    {
        $body = $response->response->body;

        return is_string($body) ? decode($body) : $body;
    }
}
