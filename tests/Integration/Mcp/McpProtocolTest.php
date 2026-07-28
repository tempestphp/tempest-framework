<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Mcp;

use Exception;
use PHPUnit\Framework\Attributes\Test;
use Tempest\Mcp\McpConfig;
use Tempest\Mcp\McpRequestHandler;
use Tempest\Mcp\McpServerDefinition;
use Tests\Tempest\Fixtures\Mcp\DemoMcpServer;
use Tests\Tempest\Fixtures\Mcp\InheritedMcpServer;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\Support\Json\encode;

/**
 * @internal
 */
final class McpProtocolTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function initialize_negotiates_the_protocol_version(): void
    {
        $connection = $this->mcp->onServer(DemoMcpServer::class);

        $response = $connection->send('initialize', [
            'protocolVersion' => '2025-06-18',
            'capabilities' => [],
            'clientInfo' => ['name' => 'test', 'version' => '1.0.0'],
        ]);

        $response->assertOk();

        $this->assertSame('2025-06-18', $response->result()['protocolVersion']);
    }

    #[Test]
    public function initialize_falls_back_to_the_latest_version_for_unsupported_versions(): void
    {
        $connection = $this->mcp->onServer(DemoMcpServer::class);

        $response = $connection->send('initialize', [
            'protocolVersion' => '1999-01-01',
        ]);

        $this->assertSame('2025-11-25', $response->result()['protocolVersion']);
    }

    #[Test]
    public function initialize_describes_the_server(): void
    {
        $connection = $this->mcp->onServer(DemoMcpServer::class);

        $result = $connection->send('initialize', ['protocolVersion' => '2025-11-25'])->result();

        $this->assertSame(['name' => 'demo-mcp-server', 'version' => '1.2.3'], $result['serverInfo']);
        $this->assertSame('A demo MCP server for testing.', $result['instructions']);
        $this->assertSame(['listChanged' => false], $result['capabilities']['tools']);
        $this->assertSame(['listChanged' => false], $result['capabilities']['resources']);
        $this->assertSame(['listChanged' => false], $result['capabilities']['prompts']);
    }

    #[Test]
    public function ping_returns_an_empty_result(): void
    {
        $connection = $this->mcp->onServer(DemoMcpServer::class);

        $response = $connection->send('ping');

        $response->assertOk();

        $this->assertSame([], $response->result());
    }

    #[Test]
    public function lists_tools_with_their_input_schemas(): void
    {
        $response = $this->mcp->onServer(DemoMcpServer::class)->listTools();

        $response
            ->assertOk()
            ->assertToolListed('add')
            ->assertToolListed('greet')
            ->assertToolListed('repeat');

        $tools = array_column($response->result()['tools'], null, 'name');

        $this->assertSame('Adds two numbers', $tools['add']['description']);
        $this->assertSame(
            [
                'type' => 'object',
                'properties' => [
                    'a' => ['type' => 'integer'],
                    'b' => ['type' => 'integer'],
                ],
                'required' => ['a', 'b'],
            ],
            $tools['add']['inputSchema'],
        );

        $this->assertSame(
            [
                'type' => 'object',
                'properties' => [
                    'name' => [
                        'type' => 'string',
                        'description' => 'The name of the person to greet',
                    ],
                    'greeting' => [
                        'type' => 'string',
                        'enum' => ['hello', 'goodbye'],
                        'default' => 'hello',
                    ],
                ],
                'required' => ['name'],
            ],
            $tools['greet']['inputSchema'],
        );

        $this->assertSame(
            [
                'type' => 'integer',
                'minimum' => 1,
                'maximum' => 5,
            ],
            $tools['rate']['inputSchema']['properties']['rating'],
        );

        $this->assertSame(['type' => 'object', 'properties' => []], [
            'type' => $tools['multi']['inputSchema']['type'],
            'properties' => $tools['multi']['inputSchema']['properties'],
        ]);
    }

    #[Test]
    public function calls_a_tool(): void
    {
        $this->mcp
            ->onServer(DemoMcpServer::class)
            ->callTool('add', ['a' => 1, 'b' => 2])
            ->assertOk()
            ->assertText('3');
    }

    #[Test]
    public function casts_enum_arguments_and_applies_defaults(): void
    {
        $connection = $this->mcp->onServer(DemoMcpServer::class);

        $connection
            ->callTool('greet', ['name' => 'Brent'])
            ->assertOk()
            ->assertText('Hello, Brent!');

        $connection
            ->callTool('greet', ['name' => 'Brent', 'greeting' => 'goodbye'])
            ->assertOk()
            ->assertText('Goodbye, Brent!');
    }

    #[Test]
    public function injects_services_into_tool_handlers(): void
    {
        $this->mcp
            ->onServer(DemoMcpServer::class)
            ->callTool('shout', ['phrase' => 'quiet'])
            ->assertOk()
            ->assertText('QUIET');
    }

    #[Test]
    public function explicit_null_arguments_are_preserved(): void
    {
        $connection = $this->mcp->onServer(DemoMcpServer::class);

        $connection->callTool('window')->assertOk()->assertText('limit: 10');
        $connection->callTool('window', ['limit' => 5])->assertOk()->assertText('limit: 5');
        $connection->callTool('window', ['limit' => null])->assertOk()->assertText('limit: NULL');
    }

    #[Test]
    public function array_arguments_are_validated(): void
    {
        $connection = $this->mcp->onServer(DemoMcpServer::class);

        $connection
            ->callTool('tally', ['entries' => ['a', 'b']])
            ->assertOk()
            ->assertText('2');
        $connection->callTool('tally', ['entries' => 'nope'])->assertError('must be an array');
        $connection->callTool('tally', ['entries' => null])->assertError('must be an array');
    }

    #[Test]
    public function inherited_tools_are_callable_on_extending_servers(): void
    {
        $connection = $this->mcp->onServer(InheritedMcpServer::class);

        $connection->callTool('base_tool')->assertOk()->assertText('base');
        $connection->callTool('shared')->assertOk()->assertText('shared');
        $connection->callTool('own_tool')->assertOk()->assertText('own');
    }

    #[Test]
    public function returns_structured_content_for_array_results(): void
    {
        $this->mcp
            ->onServer(DemoMcpServer::class)
            ->callTool('report')
            ->assertOk()
            ->assertStructured(['status' => 'ok', 'uptime' => 123])
            ->assertTextContains('"status"');
    }

    #[Test]
    public function returns_multiple_content_items(): void
    {
        $this->mcp
            ->onServer(DemoMcpServer::class)
            ->callTool('multi')
            ->assertOk()
            ->assertText('first')
            ->assertText('second');
    }

    #[Test]
    public function tool_exceptions_become_error_results(): void
    {
        $this->mcp
            ->onServer(DemoMcpServer::class)
            ->callTool('fail')
            ->assertError('Something went wrong in the tool');

        $this->exceptions->assertProcessed(Exception::class);
    }

    #[Test]
    public function validation_failures_become_error_results(): void
    {
        $this->mcp
            ->onServer(DemoMcpServer::class)
            ->callTool('rate', ['rating' => 10])
            ->assertError('rating must be between 1 and 5');
    }

    #[Test]
    public function external_tools_are_invoked_through_the_container(): void
    {
        $this->mcp
            ->onServer(DemoMcpServer::class)
            ->callTool('repeat', ['message' => 'echo'])
            ->assertOk()
            ->assertText('echo');
    }

    #[Test]
    public function prompt_handler_exceptions_are_internal_errors(): void
    {
        $response = $this->mcp
            ->onServer(DemoMcpServer::class)
            ->getPrompt('failing_prompt');

        $response->assertError('An internal error occurred');

        $this->assertSame(-32_603, $response->error()['code']);

        $this->exceptions->assertProcessed(Exception::class);
    }

    #[Test]
    public function non_object_arguments_are_invalid_params(): void
    {
        $response = $this->mcp
            ->onServer(DemoMcpServer::class)
            ->send('tools/call', ['name' => 'add', 'arguments' => [1, 2]]);

        $response->assertError('The `arguments` member must be an object');

        $this->assertSame(-32_602, $response->error()['code']);
    }

    #[Test]
    public function valid_arguments_pass_validation(): void
    {
        $this->mcp
            ->onServer(DemoMcpServer::class)
            ->callTool('rate', ['rating' => 4])
            ->assertOk()
            ->assertText('You rated 4 stars');
    }

    #[Test]
    public function missing_required_arguments_are_invalid_params(): void
    {
        $response = $this->mcp
            ->onServer(DemoMcpServer::class)
            ->callTool('add', ['a' => 1]);

        $response->assertError('The required argument `b` is missing');

        $this->assertSame(-32_602, $response->error()['code']);
    }

    #[Test]
    public function unknown_arguments_are_invalid_params(): void
    {
        $response = $this->mcp
            ->onServer(DemoMcpServer::class)
            ->callTool('add', ['a' => 1, 'b' => 2, 'c' => 3]);

        $response->assertError('The argument `c` is unknown');

        $this->assertSame(-32_602, $response->error()['code']);
    }

    #[Test]
    public function missing_tool_names_are_invalid_params(): void
    {
        $response = $this->mcp
            ->onServer(DemoMcpServer::class)
            ->send('tools/call');

        $response->assertError('The required argument `name` is missing');

        $this->assertSame(-32_602, $response->error()['code']);
    }

    #[Test]
    public function non_string_tool_names_are_invalid_params(): void
    {
        $response = $this->mcp
            ->onServer(DemoMcpServer::class)
            ->send('tools/call', ['name' => 42]);

        $response->assertError('The argument `name` must be a string');

        $this->assertSame(-32_602, $response->error()['code']);
    }

    #[Test]
    public function unknown_tools_are_invalid_params(): void
    {
        $response = $this->mcp
            ->onServer(DemoMcpServer::class)
            ->callTool('nope');

        $response->assertError('The tool `nope` does not exist');

        $this->assertSame(-32_602, $response->error()['code']);
    }

    #[Test]
    public function unknown_methods_are_method_not_found(): void
    {
        $response = $this->mcp
            ->onServer(DemoMcpServer::class)
            ->send('completion/complete');

        $response->assertError('The method `completion/complete` is not supported');

        $this->assertSame(-32_601, $response->error()['code']);
    }

    #[Test]
    public function lists_resources_and_resource_templates(): void
    {
        $connection = $this->mcp->onServer(DemoMcpServer::class);

        $resources = $connection->listResources()->assertOk()->result()['resources'];
        $resourceUris = array_column($resources, 'uri');

        $this->assertContains('demo://config', $resourceUris);
        $this->assertContains('demo://logo', $resourceUris);
        $this->assertNotContains('demo://users/{id}', $resourceUris);

        $templates = $connection->listResourceTemplates()->assertOk()->result()['resourceTemplates'];

        $this->assertSame('demo://users/{id}', $templates[0]['uriTemplate']);
        $this->assertSame('user', $templates[0]['name']);
    }

    #[Test]
    public function resource_templates_may_also_be_listed_as_resources(): void
    {
        $this->container->get(McpConfig::class)->listResourceTemplatesAsResources = true;
        $connection = $this->mcp->onServer(DemoMcpServer::class);

        $resources = $connection->listResources()->assertOk()->result()['resources'];
        $resourceUris = array_column($resources, 'uri');

        $this->assertContains('demo://users/{id}', $resourceUris);

        $templates = $connection->listResourceTemplates()->assertOk()->result()['resourceTemplates'];

        $this->assertSame('demo://users/{id}', $templates[0]['uriTemplate']);
    }

    #[Test]
    public function reads_a_resource(): void
    {
        $response = $this->mcp
            ->onServer(DemoMcpServer::class)
            ->readResource('demo://config');

        $response->assertOk();

        [$contents] = $response->result()['contents'];

        $this->assertSame('demo://config', $contents['uri']);
        $this->assertSame('application/json', $contents['mimeType']);
        $this->assertSame('{"env":"testing"}', $contents['text']);
    }

    #[Test]
    public function reads_a_templated_resource(): void
    {
        $response = $this->mcp
            ->onServer(DemoMcpServer::class)
            ->readResource('demo://users/42');

        $response->assertOk()->assertText('User #42');

        $this->assertSame('demo://users/42', $response->result()['contents'][0]['uri']);
        $this->assertSame('text/plain', $response->result()['contents'][0]['mimeType']);
    }

    #[Test]
    public function reads_a_blob_resource(): void
    {
        $response = $this->mcp
            ->onServer(DemoMcpServer::class)
            ->readResource('demo://logo');

        $response->assertOk();

        [$contents] = $response->result()['contents'];

        $this->assertSame(base64_encode('logo-bytes'), $contents['blob']);
        $this->assertSame('application/octet-stream', $contents['mimeType']);
    }

    #[Test]
    public function unknown_resources_are_resource_not_found(): void
    {
        $response = $this->mcp
            ->onServer(DemoMcpServer::class)
            ->readResource('demo://nope');

        $response->assertError('The resource `demo://nope` does not exist');

        $this->assertSame(-32_002, $response->error()['code']);
    }

    #[Test]
    public function lists_prompts_with_their_arguments(): void
    {
        $response = $this->mcp
            ->onServer(DemoMcpServer::class)
            ->listPrompts();

        $response->assertOk();

        $prompts = $response->result()['prompts'];

        $this->assertSame('review_code', $prompts[0]['name']);
        $this->assertSame('Generates a code review prompt', $prompts[0]['description']);
        $this->assertSame([['name' => 'code', 'required' => true]], $prompts[0]['arguments']);
    }

    #[Test]
    public function gets_a_prompt(): void
    {
        $response = $this->mcp
            ->onServer(DemoMcpServer::class)
            ->getPrompt('review_code', ['code' => 'echo 1;']);

        $response->assertOk()->assertText('Review the following code: echo 1;');

        $this->assertSame('user', $response->result()['messages'][0]['role']);
        $this->assertSame('text', $response->result()['messages'][0]['content']['type']);
    }

    #[Test]
    public function unknown_prompts_are_invalid_params(): void
    {
        $response = $this->mcp
            ->onServer(DemoMcpServer::class)
            ->getPrompt('nope');

        $response->assertError('The prompt `nope` does not exist');

        $this->assertSame(-32_602, $response->error()['code']);
    }

    #[Test]
    public function malformed_json_is_a_parse_error(): void
    {
        $response = $this->handler()->handle($this->server(), 'not json');

        $this->assertSame(-32_700, $response->toArray()['error']['code']);
    }

    #[Test]
    public function batches_are_rejected(): void
    {
        $batch = encode([
            ['jsonrpc' => '2.0', 'id' => 1, 'method' => 'ping'],
            ['jsonrpc' => '2.0', 'id' => 2, 'method' => 'ping'],
        ]);

        $response = $this->handler()->handle($this->server(), $batch);

        $this->assertSame(-32_600, $response->toArray()['error']['code']);
    }

    #[Test]
    public function invalid_jsonrpc_versions_are_rejected(): void
    {
        $response = $this->handler()->handle($this->server(), encode([
            'jsonrpc' => '1.0',
            'id' => 1,
            'method' => 'ping',
        ]));

        $this->assertSame(-32_600, $response->toArray()['error']['code']);
    }

    #[Test]
    public function explicit_null_ids_are_rejected(): void
    {
        $response = $this->handler()->handle($this->server(), encode([
            'jsonrpc' => '2.0',
            'id' => null,
            'method' => 'ping',
        ]));

        $this->assertSame(-32_600, $response->toArray()['error']['code']);
        $this->assertNull($response->toArray()['id']);
    }

    #[Test]
    public function notifications_produce_no_response(): void
    {
        $response = $this->handler()->handle($this->server(), encode([
            'jsonrpc' => '2.0',
            'method' => 'notifications/cancelled',
        ]));

        $this->assertNull($response);
    }

    private function handler(): McpRequestHandler
    {
        return $this->container->get(McpRequestHandler::class);
    }

    private function server(): McpServerDefinition
    {
        return $this->container->get(McpConfig::class)->servers[DemoMcpServer::class];
    }
}
