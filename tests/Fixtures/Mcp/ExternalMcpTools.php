<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Mcp;

use Tempest\Mcp\McpTool;

final readonly class ExternalMcpTools
{
    #[McpTool(server: DemoMcpServer::class, description: 'Repeats a message')]
    public function repeat(string $message): string
    {
        return $message;
    }
}
