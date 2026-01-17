<?php

declare(strict_types=1);

namespace Tempest\Generation\TypeScript;

/**
 * Interface for writing TypeScript type definitions to different output formats.
 * Implementations receive their configuration (destination, options, etc.) via constructor injection.
 */
interface TypeScriptWriter
{
    /**
     * Write the TypeScript output.
     */
    public function write(TypeScriptOutput $output): void;
}
