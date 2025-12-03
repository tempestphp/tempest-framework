<?php

namespace Tempest\Testing;

use Attribute;
use ReflectionMethod;
use Tempest\Reflection\MethodReflector;

use function Tempest\Support\arr;

#[Attribute(Attribute::TARGET_METHOD)]
final class Test
{
    public MethodReflector $handler;

    /** @var MethodReflector[] */
    public array $before = [];

    /** @var MethodReflector[] */
    public array $after = [];

    /** @var array[] */
    public ?array $provide = null;

    public string $name {
        get => $this->handler->getDeclaringClass()->getName() . '::' . $this->handler->getName();
    }

    public static function fromName(string $name): self
    {
        $reflector = new MethodReflector(new ReflectionMethod(...explode('::', $name)));

        return self::fromReflector($reflector);
    }

    public static function fromReflector(MethodReflector $reflector): self
    {
        $self = new self();

        $self->handler = $reflector;

        $self->before = arr($reflector->getDeclaringClass()->getPublicMethods())
            ->filter(fn (MethodReflector $otherMethod) => $otherMethod->hasAttribute(Before::class))
            ->values()
            ->toArray();

        $self->after = arr($reflector->getDeclaringClass()->getPublicMethods())
            ->filter(fn (MethodReflector $otherMethod) => $otherMethod->hasAttribute(After::class))
            ->values()
            ->reverse()
            ->toArray();

        $self->provide = $reflector->getAttribute(Provide::class)?->entries;

        return $self;
    }

    public function matchesFilter(string $filter): bool
    {
        return str_contains($this->name, $filter);
    }
}
