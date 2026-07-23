<?php

declare(strict_types=1);

namespace Tempest\Mcp\Content;

use Tempest\Mcp\Exceptions\ContentWasNotSupported;

final readonly class ResourceLink implements Content
{
    public function __construct(
        public string $uri,
        public string $name,
        public ?string $description = null,
        public ?string $mimeType = null,
    ) {}

    public function toContent(): array
    {
        return [
            'type' => 'resource_link',
            'uri' => $this->uri,
            'name' => $this->name,
            ...($this->description === null ? [] : ['description' => $this->description]),
            ...($this->mimeType === null ? [] : ['mimeType' => $this->mimeType]),
        ];
    }

    public function toResourceContents(string $uri, ?string $mimeType): array
    {
        throw ContentWasNotSupported::becauseResourceLinksCannotBeUsedInResources();
    }
}
