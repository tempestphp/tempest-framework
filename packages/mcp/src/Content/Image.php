<?php

declare(strict_types=1);

namespace Tempest\Mcp\Content;

final readonly class Image implements Content
{
    /**
     * @param string $data The raw binary contents of the image.
     * @param string|null $mimeType The mime type of the image. When not specified, falls back to the mime type declared on the resource, or `image/png`.
     */
    public function __construct(
        public string $data,
        public ?string $mimeType = null,
    ) {}

    public function toContent(): array
    {
        return [
            'type' => 'image',
            'data' => base64_encode($this->data),
            'mimeType' => $this->mimeType ?? 'image/png',
        ];
    }

    public function toResourceContents(string $uri, ?string $mimeType): array
    {
        return [
            'uri' => $uri,
            'mimeType' => $this->mimeType ?? $mimeType ?? 'image/png',
            'blob' => base64_encode($this->data),
        ];
    }
}
