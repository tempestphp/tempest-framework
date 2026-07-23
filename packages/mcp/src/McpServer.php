<?php

declare(strict_types=1);

namespace Tempest\Mcp;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class McpServer
{
    public function __construct(
        public ?string $name = null,
        public ?string $version = null,
        public ?string $instructions = null,
        public ?string $path = null,
    ) {}
}
