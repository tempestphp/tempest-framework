<?php

declare(strict_types=1);

namespace Tempest\Generation\TypeScript\Writers;

use Tempest\Generation\TypeScript\TypeResolver;
use Tempest\Generation\TypeScript\TypeScriptGenerationConfig;

/**
 * Writes TypeScript definitions to a single `.d.ts` file using TypeScript namespaces.
 */
final class NamespacedTypeScriptGenerationConfig implements TypeScriptGenerationConfig
{
    private(set) string $writer = NamespacedFileWriter::class;

    /** @var array<class-string> */
    public array $sources = [];

    /** @var array<class-string<TypeResolver>> */
    public array $resolvers = [];

    /**
     * @param string $filename The output filename for the generated TypeScript definitions.
     */
    public function __construct(
        public readonly string $filename,
    ) {}
}
