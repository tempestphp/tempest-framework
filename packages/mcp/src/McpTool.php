<?php

declare(strict_types=1);

namespace Tempest\Mcp;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
final readonly class McpTool
{
    /**
     * @param class-string|null $server The `#[McpServer]` class this tool belongs to. May be omitted when the tool lives inside an `#[McpServer]` class.
     */
    public function __construct(
        public ?string $name = null,
        public ?string $description = null,
        public ?string $server = null,
    ) {}
}
