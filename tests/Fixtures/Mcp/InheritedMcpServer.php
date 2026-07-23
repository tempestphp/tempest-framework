<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Mcp;

use Tempest\Mcp\McpServer;
use Tempest\Mcp\McpTool;

#[McpServer]
final class InheritedMcpServer extends BaseMcpServer
{
    #[McpTool]
    public function ownTool(): string
    {
        return 'own';
    }
}
