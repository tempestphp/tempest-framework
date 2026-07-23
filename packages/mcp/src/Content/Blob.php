<?php

declare(strict_types=1);

namespace Tempest\Mcp\Content;

use Tempest\Mcp\Exceptions\ContentWasNotSupported;

final readonly class Blob implements Content
{
    /**
     * @param string $contents The raw binary contents of the blob.
     */
    public function __construct(
        public string $contents,
    ) {}

    public function toContent(): array
    {
        throw ContentWasNotSupported::becauseBlobsCannotBeUsedOutsideResources();
    }

    public function toResourceContents(string $uri, ?string $mimeType): array
    {
        return [
            'uri' => $uri,
            'mimeType' => $mimeType ?? 'application/octet-stream',
            'blob' => base64_encode($this->contents),
        ];
    }
}
