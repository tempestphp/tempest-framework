<?php

declare(strict_types=1);

namespace Tempest\Reflection;

use Error;
use PropertyHookType;
use ReflectionMethod;
use ReflectionProperty as PHPReflectionProperty;
use Stringable;

final readonly class PropertyReflector implements Reflector, Stringable
{
    use HasAttributes;

    public function __construct(
        private PHPReflectionProperty $reflectionProperty,
    ) {}

    public static function fromParts(string|object $class, string $name): self
    {
        return new self(new PHPReflectionProperty($class, $name));
    }

    public function getReflection(): PHPReflectionProperty
    {
        return $this->reflectionProperty;
    }

    public function getValue(object $object): mixed
    {
        return $this->reflectionProperty->getValue($object);
    }

    public function setValue(object $object, mixed $value): void
    {
        $this->reflectionProperty->setValue($object, $value);
    }

    public function isInitialized(object $object): bool
    {
        return $this->reflectionProperty->isInitialized($object);
    }

    public function accepts(mixed $input): bool
    {
        return $this->getType()->accepts($input);
    }

    public function getClass(): ClassReflector
    {
        return new ClassReflector($this->reflectionProperty->getDeclaringClass());
    }

    public function getType(): TypeReflector
    {
        return new TypeReflector($this->reflectionProperty);
    }

    public function isIterable(): bool
    {
        return $this->getType()->isIterable();
    }

    public function isPromoted(): bool
    {
        return $this->reflectionProperty->isPromoted();
    }

    public function isNullable(): bool
    {
        return $this->getType()->isNullable();
    }

    public function isPrivate(): bool
    {
        return $this->reflectionProperty->isPrivate();
    }

    public function isProtected(): bool
    {
        return $this->reflectionProperty->isProtected();
    }

    public function isPublic(): bool
    {
        return $this->reflectionProperty->isPublic();
    }

    public function isReadonly(): bool
    {
        return $this->reflectionProperty->isReadOnly();
    }

    public function getIterableType(): ?TypeReflector
    {
        $doc = $this->reflectionProperty->getDocComment();

        if ($doc === false) {
            return null;
        }

        /** @var array<class-string, array<string, string|null>> $types */
        static $types = [];

        $class = $this->reflectionProperty->getDeclaringClass()->getName();
        $property = $this->reflectionProperty->getName();

        if (! array_key_exists($property, $types[$class] ?? [])) {
            $types[$class][$property] = $this->resolveIterableTypeName($doc);
        }

        $name = $types[$class][$property];

        return $name === null ? null : new TypeReflector($name);
    }

    private function resolveIterableTypeName(string $doc): ?string
    {
        if (preg_match('/@var\s+([\\\\\w]+)\[\]/', $doc, $match)) {
            return $this->resolveDocblockClassName($match[1]);
        }

        if (preg_match('/@var\s+(?:list|array)<([\\\\\w]+)>/', $doc, $match)) {
            return $this->resolveDocblockClassName($match[1]);
        }

        return null;
    }

    private function resolveDocblockClassName(string $name): string
    {
        if (in_array(
            $name,
            ['array', 'bool', 'callable', 'false', 'float', 'int', 'iterable', 'mixed', 'never', 'null', 'object', 'resource', 'string', 'true', 'void'],
            strict: true,
        )) {
            return $name;
        }

        if (str_starts_with($name, '\\')) {
            return ltrim($name, '\\');
        }

        $segments = explode('\\', $name);
        $firstSegment = strtolower($segments[0]);
        $context = DocblockContext::forProperty($this->reflectionProperty);
        $aliases = $context['aliases'];

        if ($firstSegment === 'namespace') {
            $name = implode('\\', array_slice($segments, 1));
        }

        if ($firstSegment !== 'namespace' && isset($aliases[$firstSegment])) {
            return implode('\\', [$aliases[$firstSegment], ...array_slice($segments, 1)]);
        }

        $namespace = $context['namespace'];

        if ($namespace === '') {
            return $name;
        }

        return $namespace . '\\' . $name;
    }

    public function isUninitialized(object $object): bool
    {
        return ! $this->reflectionProperty->isInitialized($object);
    }

    public function isVirtual(): bool
    {
        return $this->reflectionProperty->isVirtual();
    }

    public function isHooked(): bool
    {
        return $this->getGetHook() || $this->getSetHook();
    }

    public function getGetHook(): ?MethodReflector
    {
        $hook = $this->reflectionProperty->getHook(PropertyHookType::Get);

        if (! $hook instanceof ReflectionMethod) {
            return null;
        }

        return new MethodReflector($hook);
    }

    public function getSetHook(): ?MethodReflector
    {
        $hook = $this->reflectionProperty->getHook(PropertyHookType::Set);

        if (! $hook instanceof ReflectionMethod) {
            return null;
        }

        return new MethodReflector($hook);
    }

    public function unset(object $object): void
    {
        unset($object->{$this->getName()});
    }

    public function set(object $object, mixed $value): void
    {
        $this->reflectionProperty->setValue($object, $value);
    }

    public function get(object $object, mixed $default = null): mixed
    {
        try {
            return $this->reflectionProperty->getValue($object) ?? $default;
        } catch (Error $error) {
            return $default ?? throw $error;
        }
    }

    public function getName(): string
    {
        return $this->reflectionProperty->getName();
    }

    public function hasDefaultValue(): bool
    {
        $constructorParameters = [];

        foreach ($this->getClass()->getConstructor()?->getParameters() ?? [] as $parameter) {
            $constructorParameters[$parameter->getName()] = $parameter;
        }

        $hasDefaultValue = $this->reflectionProperty->hasDefaultValue();

        $hasPromotedDefaultValue = $this->isPromoted() && isset($constructorParameters[$this->getName()]) && $constructorParameters[$this->getName()]->isDefaultValueAvailable();

        return $hasDefaultValue || $hasPromotedDefaultValue;
    }

    public function __toString(): string
    {
        return $this->getClass()->getName() . '::' . $this->getName();
    }
}
