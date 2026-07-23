<?php

declare(strict_types=1);

namespace Tempest\Mcp;

final class McpServerDefinition
{
    /** @var array<string, ToolDefinition> */
    public array $tools = [];

    /** @var array<string, PromptDefinition> */
    public array $prompts = [];

    /** @var array<string, ResourceDefinition> */
    public array $resources = [];

    /** @var array<string, ResourceDefinition> */
    public array $resourceTemplates = [];

    public function __construct(
        public readonly string $class,
        public readonly string $name,
        public readonly string $version,
        public readonly ?string $instructions,
        public readonly ?string $path,
    ) {}
}
