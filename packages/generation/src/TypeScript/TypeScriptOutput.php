<?php

declare(strict_types=1);

namespace Tempest\Generation\TypeScript;

/**
 * Represents the output of TypeScript code generation.
 */
final readonly class TypeScriptOutput
{
    /**
     * @param array<string,TypeDefinition[]> $namespaces Type definitions grouped by namespace
     * @param array<string> $imports Additional import statements to include
     */
    public function __construct(
        public array $namespaces = [],
        public array $imports = [],
    ) {}

    /**
     * Gets all type definitions across all namespaces.
     *
     * @return TypeDefinition[]
     */
    public function getAllDefinitions(): array
    {
        $definitions = [];

        foreach ($this->namespaces as $namespace => $namespaceDefinitions) {
            $definitions = [...$definitions, ...$namespaceDefinitions];
        }

        return $definitions;
    }

    /**
     * Gets all namespace names.
     *
     * @return string[]
     */
    public function getNamespaces(): array
    {
        return array_keys($this->namespaces);
    }

    /**
     * Gets definitions for a specific namespace.
     *
     * @return TypeDefinition[]
     */
    public function getDefinitionsForNamespace(string $namespace): array
    {
        return $this->namespaces[$namespace] ?? [];
    }

    /**
     * Checks if output has any definitions.
     */
    public function isEmpty(): bool
    {
        return $this->namespaces === [];
    }
}
