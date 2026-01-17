<?php

declare(strict_types=1);

namespace Tempest\Generation\TypeScript;

final class TypesRepository
{
    /**
     * @var array<string, TypeDefinition|InterfaceDefinition>
     */
    private array $definitions = [];

    /**
     * Add a type definition to the repository.
     */
    public function add(TypeDefinition|InterfaceDefinition $definition): void
    {
        $this->definitions[$definition->class] = $definition;
    }

    /**
     * Get a type definition by class name.
     */
    public function get(string $class): TypeDefinition|InterfaceDefinition|null
    {
        return $this->definitions[$class] ?? null;
    }

    /**
     * Check if a definition exists for the given class.
     */
    public function has(string $class): bool
    {
        return isset($this->definitions[$class]);
    }

    /**
     * Get all type definitions.
     *
     * @return array<TypeDefinition|InterfaceDefinition>
     */
    public function getAll(): array
    {
        return array_values($this->definitions);
    }

    /**
     * Clear all definitions.
     */
    public function clear(): void
    {
        $this->definitions = [];
    }
}
