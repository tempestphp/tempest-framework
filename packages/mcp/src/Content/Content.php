<?php

declare(strict_types=1);

namespace Tempest\Mcp\Content;

interface Content
{
    /**
     * The wire shape used in tool results and prompt messages.
     */
    public function toContent(): array;

    /**
     * The wire shape used in `resources/read` results.
     */
    public function toResourceContents(string $uri, ?string $mimeType): array;
}
