<?php

declare(strict_types=1);

namespace Tempest\Vite\Manifest;

final readonly class Chunk
{
    public function __construct(
        public string $file,
        public ?string $src,
        public bool $isEntry,
        public bool $isDynamicEntry,
        public bool $isLegacyEntry,
        public array $css,
        public array $imports,
        public array $dynamicImports,
        public array $assets,
        public ?string $integrity = null,
    ) {
    }

    public static function fromArray(array $manifestEntry): static
    {
        $file = $manifestEntry['file'] ?? '';
        $isEntry = $manifestEntry['isEntry'] ?? false;
        $isLegacyEntry = str_contains($file, '-legacy');

        return new static(
            file: $file,
            src: $manifestEntry['src'] ?? null,
            isEntry: $isEntry,
            isDynamicEntry: $manifestEntry['isDynamicEntry'] ?? false,
            isLegacyEntry: $isLegacyEntry,
            css: $manifestEntry['css'] ?? [],
            imports: $manifestEntry['imports'] ?? [],
            dynamicImports: $manifestEntry['dynamicImports'] ?? [],
            assets: $manifestEntry['assets'] ?? [],
            integrity: $manifestEntry['integrity'] ?? null,
        );
    }
}
