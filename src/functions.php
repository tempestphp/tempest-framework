<?php

declare(strict_types=1);

namespace {

    use function Tempest\get;
    use Tempest\Support\VarExport\Debug;

    if (! function_exists('lw')) {
        function lw(mixed ...$input): void
        {
            get(Debug::class)->log($input);
        }
    }

    if (! function_exists('ld')) {
        function ld(mixed ...$input): void
        {
            get(Debug::class)->log($input);
            die();
        }
    }

    if (! function_exists('ll')) {
        function ll(mixed ...$input): void
        {
            get(Debug::class)->log($input, writeToOut: false);
        }
    }
}

namespace Tempest {

    use ReflectionType;
    use Reflector;
    use Tempest\Container\GenericContainer;
    use Tempest\Events\EventBus;
    use Tempest\Mapper\ObjectFactory;
    use Tempest\Support\Reflection\Attributes;
    use Tempest\Support\Reflection\TypeName;

    /**
     * @template TClassName
     * @param class-string<TClassName> $className
     * @param string|null $tag
     * @param mixed ...$params
     * @return TClassName
     */
    function get(string $className, ?string $tag = null, mixed ...$params): object
    {
        $container = GenericContainer::instance();

        return $container->get($className, $tag, ...$params);
    }

    function event(object $event): void
    {
        $eventBus = get(EventBus::class);

        $eventBus->dispatch($event);
    }

    /**
     * @template T of object
     * @param class-string<T> $attributeName
     * @return \Tempest\Support\Reflection\Attributes<T>
     */
    function attribute(string $attributeName): Attributes
    {
        return Attributes::find($attributeName);
    }

    /**
     * @template T of object
     * @param T|class-string<T> $objectOrClass
     * @return ObjectFactory<T>
     */
    function make(object|string $objectOrClass): ObjectFactory
    {
        $factory = get(ObjectFactory::class);

        return $factory->forClass($objectOrClass);
    }

    function map(mixed $data): ObjectFactory
    {
        $factory = get(ObjectFactory::class);

        return $factory->withData($data);
    }

    function type(Reflector|ReflectionType $reflector): string
    {
        return (new TypeName())->resolve($reflector);
    }
}
