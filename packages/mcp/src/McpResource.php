<?php

declare(strict_types=1);

namespace Tempest\Mcp;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
final readonly class McpResource
{
    /**
     * @param string $uri The URI of the resource. May contain template variables — `users/{id}` — that correspond to method parameters.
     * @param class-string|null $server The `#[McpServer]` class this resource belongs to. May be omitted when the resource lives inside an `#[McpServer]` class.
     */
    public function __construct(
        public string $uri,
        public ?string $name = null,
        public ?string $description = null,
        public ?string $mimeType = null,
        public ?string $server = null,
    ) {}
}
