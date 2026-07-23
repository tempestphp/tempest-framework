<?php

declare(strict_types=1);

namespace Tempest\Mcp;

use Tempest\Reflection\MethodReflector;

final readonly class ResourceDefinition
{
    public UriTemplate $uriTemplate;

    public function __construct(
        public string $uri,
        public string $name,
        public ?string $description,
        public ?string $mimeType,
        public string $class,
        public MethodReflector $handler,
    ) {
        $this->uriTemplate = new UriTemplate($uri);
    }

    public function isTemplated(): bool
    {
        return $this->uriTemplate->isTemplated();
    }
}
