<?php

declare(strict_types=1);

namespace Tempest\Generation\TypeScript;

use Tempest\Generation\TypeScript\StructureResolvers\ClassStructureResolver;
use Tempest\Generation\TypeScript\StructureResolvers\EnumStructureResolver;
use Tempest\Reflection\TypeReflector;

final class GenericTypeScriptGenerator implements TypeScriptGenerator
{
    private ?TypesRepository $repository = null;

    public function __construct(
        private readonly TypeScriptGenerationConfig $config,
        private readonly ClassStructureResolver $classResolver,
        private readonly EnumStructureResolver $enumResolver,
    ) {}

    public function generate(): TypeScriptOutput
    {
        $this->repository = new TypesRepository();

        foreach ($this->config->sources as $className) {
            $this->include($className);
        }

        $grouped = [];

        foreach ($this->repository->getAll() as $definition) {
            $namespace = $definition->namespace;
            $grouped[$namespace] ??= [];
            $grouped[$namespace][] = $definition;
        }

        ksort($grouped);

        return new TypeScriptOutput(
            namespaces: $grouped,
        );
    }

    public function include(string $className): void
    {
        if ($this->repository->has($className)) {
            return;
        }

        $type = new TypeReflector($className);

        if ($type->isEnum()) {
            $this->repository->add($this->enumResolver->resolve($type, $this));
            return;
        }

        if ($type->isClass() || $type->isInterface()) {
            $this->repository->add($this->classResolver->resolve($type, $this));
            return;
        }
    }
}
