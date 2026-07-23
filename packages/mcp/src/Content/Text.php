<?php

declare(strict_types=1);

namespace Tempest\Mcp\Content;

final readonly class Text implements Content
{
    public function __construct(
        public string $text,
    ) {}

    public function toContent(): array
    {
        return [
            'type' => 'text',
            'text' => $this->text,
        ];
    }

    public function toResourceContents(string $uri, ?string $mimeType): array
    {
        return [
            'uri' => $uri,
            'mimeType' => $mimeType ?? 'text/plain',
            'text' => $this->text,
        ];
    }
}
