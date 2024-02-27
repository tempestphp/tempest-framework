<?php

declare(strict_types=1);

namespace Tempest\Container;

use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionUnionType;
use Reflector;

final readonly class ContainerLogItem
{
    public function __construct(
        public string $id,
        public ?Reflector $subject = null
    ) {
    }

    public function __toString(): string
    {
        $subjectType = $this->subject ? $this->subject::class : null;

        return match ($subjectType) {
            ReflectionParameter::class => $this->formatParameter(),
            ReflectionMethod::class => $this->formatMethod(),
            ReflectionClass::class => $this->formatClass(),
            default => $this->id,
        };
    }

    private function formatParameter(): string
    {
        $type = $this->shortTypeName($this->subject->getType());

        return " [unresolved parameter: {$type} \${$this->subject->getName()}]";
    }

    private function formatMethod(): string
    {
        return "::{$this->subject->getName()}()";
    }

    private function formatClass(): string
    {
        return PHP_EOL . "└── {$this->subject->getName()}";
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
