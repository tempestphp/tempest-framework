<?php

declare(strict_types=1);

namespace Tempest\Mcp\Testing;

use PHPUnit\Framework\Assert;
use stdClass;
use Tempest\Mcp\McpRequestHandler;
use Tempest\Mcp\McpServerDefinition;
use Tempest\Mcp\ProtocolVersion;

use function Tempest\Support\Json\decode;
use function Tempest\Support\Json\encode;

final class McpTestConnection
{
    private int $lastId = 0;

    public function __construct(
        private readonly McpRequestHandler $requestHandler,
        private readonly McpServerDefinition $server,
    ) {}

    public function initialize(): McpTestResponse
    {
        $response = $this->send('initialize', [
            'protocolVersion' => ProtocolVersion::LATEST->value,
            'capabilities' => new stdClass(),
            'clientInfo' => [
                'name' => 'tempest-test-client',
                'version' => '1.0.0',
            ],
        ]);

        $this->notify('notifications/initialized');

        return $response;
    }

    public function send(string $method, array|object $params = []): McpTestResponse
    {
        $response = $this->requestHandler->handle($this->server, encode([
            'jsonrpc' => '2.0',
            'id' => ++$this->lastId,
            'method' => $method,
            ...($params === [] ? [] : ['params' => $params]),
        ]));

        Assert::assertNotNull($response, "Expected a response for the `{$method}` request, but none was returned.");

        return new McpTestResponse(decode(encode($response->toArray())));
    }

    public function notify(string $method, array|object $params = []): void
    {
        $response = $this->requestHandler->handle($this->server, encode([
            'jsonrpc' => '2.0',
            'method' => $method,
            ...($params === [] ? [] : ['params' => $params]),
        ]));

        Assert::assertNull($response, "Expected no response for the `{$method}` notification, but one was returned.");
    }

    public function callTool(string $name, array $arguments = []): McpTestResponse
    {
        return $this->send('tools/call', [
            'name' => $name,
            ...($arguments === [] ? [] : ['arguments' => $arguments]),
        ]);
    }

    public function getPrompt(string $name, array $arguments = []): McpTestResponse
    {
        return $this->send('prompts/get', [
            'name' => $name,
            ...($arguments === [] ? [] : ['arguments' => $arguments]),
        ]);
    }

    public function readResource(string $uri): McpTestResponse
    {
        return $this->send('resources/read', ['uri' => $uri]);
    }

    public function listTools(): McpTestResponse
    {
        return $this->send('tools/list');
    }

    public function listResources(): McpTestResponse
    {
        return $this->send('resources/list');
    }

    public function listResourceTemplates(): McpTestResponse
    {
        return $this->send('resources/templates/list');
    }

    public function listPrompts(): McpTestResponse
    {
        return $this->send('prompts/list');
    }
}
