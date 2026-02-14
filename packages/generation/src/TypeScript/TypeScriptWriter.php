<?php

declare(strict_types=1);

namespace Tempest\Generation\TypeScript;

/**
 * Responsible for writing TypeScript type definitions to different output formats.
 */
interface TypeScriptWriter
{
    /**
     * Writes the TypeScript output.
     */
    public function write(TypeScriptOutput $output): void;
}
