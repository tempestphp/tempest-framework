<?php

declare(strict_types=1);

namespace Tempest\Container;

use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionUnionType;

final readonly class ContainerLogItem
{
    public function __construct(
        public string $id,
        public ?ReflectionClass $class = null,
        public ?ReflectionMethod $method = null,
        public ?ReflectionParameter $parameter = null,
    ) {
    }

    public function __toString(): string
    {
        return match (true) {
            $this->parameter !== null => $this->parameterAsString(),
            $this->method !== null => "{$this->method->getDeclaringClass()->getName()}::{$this->method->getName()}()",
            $this->class !== null => "{$this->class->getName()}",
            default => $this->id,
        };
    }

    private function parameterAsString(): string
    {
        $type = $this->shortTypeName($this->parameter->getType());
        $class = $this->shortTypeName($this->parameter->getDeclaringClass());

        return "{$type} \${$this->parameter->getName()} in {$class}::__construct()";
    }

    private function shortTypeName(ReflectionNamedType|ReflectionUnionType|ReflectionClass $type): string
    {
        $name = match($type::class) {
            ReflectionNamedType::class => $type->getName(),
            ReflectionUnionType::class => implode('|', array_map(
                fn (ReflectionNamedType $namedType) => $this->shortTypeName($namedType),
                $type->getTypes(),
            )),
            ReflectionClass::class => $type->getName(),
        };

        $parts = explode('\\', $name);

        return $parts[array_key_last($parts)];
    }
}
