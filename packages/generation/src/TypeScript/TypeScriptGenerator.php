<?php

declare(strict_types=1);

namespace Tempest\Generation\TypeScript;

/**
 * Generates TypeScript type definitions from PHP classes.
 */
interface TypeScriptGenerator
{
    /**
     * Generates TypeScript definitions and return the output.
     */
    public function generate(): TypeScriptOutput;

    /**
     * Ensures a specific class has been generated.
     */
    public function include(string $className): void;
}
