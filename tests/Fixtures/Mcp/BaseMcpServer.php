<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Mcp;

use Tempest\Mcp\McpServer;
use Tempest\Mcp\McpTool;

#[McpServer]
class BaseMcpServer extends AbstractMcpTools
{
    #[McpTool]
    public function baseTool(): string
    {
        return 'base';
    }
}
