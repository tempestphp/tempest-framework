<?php

declare(strict_types=1);

namespace Tempest\Mcp\Tests\Fixtures;

abstract class SharedToolsFixture
{
    public function sharedTool(): string
    {
        return 'shared';
    }
}
