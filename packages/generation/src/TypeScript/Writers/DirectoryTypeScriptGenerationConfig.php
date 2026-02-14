<?php

declare(strict_types=1);

namespace Tempest\Generation\TypeScript\Writers;

use Tempest\Generation\TypeScript\TypeResolver;
use Tempest\Generation\TypeScript\TypeScriptGenerationConfig;

/**
 * Writes TypeScript definitions to separate `.ts` files organized by namespace in a directory structure.
 */
final class DirectoryTypeScriptGenerationConfig implements TypeScriptGenerationConfig
{
    private(set) string $writer = DirectoryWriter::class;

    /** @var array<class-string> */
    public array $sources = [];

    /** @var array<class-string<TypeResolver>> */
    public array $resolvers = [];

    /**
     * @param string $directory The output directory for the generated TypeScript files.
     */
    public function __construct(
        public readonly string $directory,
    ) {}
}
