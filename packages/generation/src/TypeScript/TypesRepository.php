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
     * Adds a type definition to the repository.
     */
    public function add(TypeDefinition|InterfaceDefinition $definition): void
    {
        $this->definitions[$definition->class] = $definition;
    }

    /**
     * Gets a type definition by class name.
     */
    public function get(string $class): TypeDefinition|InterfaceDefinition|null
    {
        return $this->definitions[$class] ?? null;
    }

    /**
     * Checks if a definition exists for the given class.
     */
    public function has(string $class): bool
    {
        return isset($this->definitions[$class]);
    }

    /**
     * Gets all type definitions.
     *
     * @return array<TypeDefinition|InterfaceDefinition>
     */
    public function getAll(): array
    {
        return array_values($this->definitions);
    }
}
