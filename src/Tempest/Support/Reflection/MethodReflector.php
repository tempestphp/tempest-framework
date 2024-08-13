<?php

declare(strict_types=1);

namespace Tempest\Support\Reflection;

use Generator;
use ReflectionMethod as PHPReflectionMethod;

final readonly class MethodReflector implements Reflector
{
    use HasAttributes;

    public function __construct(
        private PHPReflectionMethod $reflectionMethod,
    ) {
    }

    public static function fromParts(string|object $class, string $name): self
    {
        return new self(new PHPReflectionMethod($class, $name));
    }

    public function getReflection(): PHPReflectionMethod
    {
        return $this->reflectionMethod;
    }

    /** @return Generator|\Tempest\Support\Reflection\ParameterReflector[] */
    public function getParameters(): Generator
    {
        foreach ($this->reflectionMethod->getParameters() as $parameter) {
            yield new ParameterReflector($parameter);
        }
    }

    public function invokeArgs(object|null $object, array $args = []): mixed
    {
        return $this->reflectionMethod->invokeArgs($object, $args);
    }

    public function getReturnType(): TypeReflector
    {
        return new TypeReflector($this->reflectionMethod->getReturnType());
    }

    public function getDeclaringClass(): ClassReflector
    {
        return new ClassReflector($this->reflectionMethod->getDeclaringClass());
    }

    public function getName(): string
    {
        return $this->reflectionMethod->getName();
    }

    public function getShortName(): string
    {
        $string = $this->getDeclaringClass()->getShortName() . '::' . $this->getName() . '(';

        $parameters = [];

        foreach ($this->getParameters() as $parameter) {
            $parameters[] = $parameter->getType()->getShortName() . ' $' . $parameter->getName();
        }

        $string .= implode(', ', $parameters);

        return $string . ')';
    }

    public function __serialize(): array
    {
        return [
            'class' => $this->getReflection()->getDeclaringClass()->getName(),
            'method' => $this->getReflection()->getName(),
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->reflectionMethod = new PHPReflectionMethod(
            objectOrMethod: $data['class'],
            method: $data['method'],
        );
    }
}
