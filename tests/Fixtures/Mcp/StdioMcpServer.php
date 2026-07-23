<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Mcp;

use Tempest\Mcp\McpServer;
use Tempest\Mcp\McpTool;

#[McpServer(name: 'stdio-demo')]
final class StdioMcpServer
{
    #[McpTool]
    public function pong(): string
    {
        return 'pong';
    }
}
