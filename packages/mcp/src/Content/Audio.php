<?php

declare(strict_types=1);

namespace Tempest\Mcp\Content;

final readonly class Audio implements Content
{
    /**
     * @param string $data The raw binary contents of the audio fragment.
     * @param string|null $mimeType The mime type of the audio fragment. When not specified, falls back to the mime type declared on the resource, or `audio/wav`.
     */
    public function __construct(
        public string $data,
        public ?string $mimeType = null,
    ) {}

    public function toContent(): array
    {
        return [
            'type' => 'audio',
            'data' => base64_encode($this->data),
            'mimeType' => $this->mimeType ?? 'audio/wav',
        ];
    }

    public function toResourceContents(string $uri, ?string $mimeType): array
    {
        return [
            'uri' => $uri,
            'mimeType' => $this->mimeType ?? $mimeType ?? 'audio/wav',
            'blob' => base64_encode($this->data),
        ];
    }
}
