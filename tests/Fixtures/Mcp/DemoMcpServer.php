<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Mcp;

use Exception;
use Tempest\Mcp\Content\Blob;
use Tempest\Mcp\Content\Text;
use Tempest\Mcp\Description;
use Tempest\Mcp\McpPrompt;
use Tempest\Mcp\McpResource;
use Tempest\Mcp\McpServer;
use Tempest\Mcp\McpTool;
use Tempest\Validation\Rules\IsBetween;

#[McpServer(version: '1.2.3', instructions: 'A demo MCP server for testing.', path: '/mcp/demo')]
final class DemoMcpServer
{
    #[McpTool(description: 'Adds two numbers')]
    public function add(int $a, int $b): int
    {
        return $a + $b;
    }

    #[McpTool(name: 'greet')]
    public function greetSomeone(
        #[Description('The name of the person to greet')]
        string $name,
        Greeting $greeting = Greeting::HELLO,
    ): string {
        return ucfirst($greeting->value) . ", {$name}!";
    }

    #[McpTool]
    public function rate(
        #[IsBetween(min: 1, max: 5)]
        int $rating,
    ): string {
        return "You rated {$rating} stars";
    }

    #[McpTool]
    public function report(): array
    {
        return ['status' => 'ok', 'uptime' => 123];
    }

    #[McpTool]
    public function fail(): string
    {
        throw new Exception('Something went wrong in the tool');
    }

    #[McpTool]
    public function shout(string $phrase, StringManipulator $manipulator): string
    {
        return $manipulator->upper($phrase);
    }

    #[McpTool]
    public function multi(): array
    {
        return [new Text('first'), new Text('second')];
    }

    #[McpTool]
    public function window(?int $limit = 10): string
    {
        return 'limit: ' . var_export($limit, true);
    }

    #[McpTool]
    public function tally(array $entries): int
    {
        return count($entries);
    }

    #[McpPrompt(description: 'Generates a code review prompt')]
    public function reviewCode(string $code): string
    {
        return "Review the following code: {$code}";
    }

    #[McpResource(uri: 'demo://config', description: 'The demo configuration', mimeType: 'application/json')]
    public function config(): array
    {
        return ['env' => 'testing'];
    }

    #[McpResource(uri: 'demo://users/{id}')]
    public function user(int $id): string
    {
        return "User #{$id}";
    }

    #[McpResource(uri: 'demo://logo')]
    public function logo(): Blob
    {
        return new Blob('logo-bytes');
    }

    #[McpPrompt]
    public function failingPrompt(): string
    {
        throw new Exception('Prompt failure');
    }
}
