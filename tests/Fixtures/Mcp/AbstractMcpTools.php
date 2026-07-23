<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Mcp;

use Tempest\Mcp\McpTool;

abstract class AbstractMcpTools
{
    #[McpTool]
    public function shared(): string
    {
        return 'shared';
    }
}
