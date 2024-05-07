<?php

declare(strict_types=1);

namespace Tempest
{
    use Tempest\Container\GenericContainer;
    use Tempest\Events\EventBus;
    use Tempest\Mapper\ObjectMapper;
    use Tempest\Support\Reflection\Attributes;

    /**
     * @template TClassName
     * @param class-string<TClassName> $className
     * @return TClassName
     */
    function get(string $className): object
    {
        $container = GenericContainer::instance();

        return $container->get($className);
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
     * @return ObjectMapper<T>
     */
    function make(object|string $objectOrClass): ObjectMapper
    {
        $factory = get(ObjectMapper::class);

        return $factory->forClass($objectOrClass);
    }

    function map(mixed $data): ObjectMapper
    {
        $factory = get(ObjectMapper::class);

        return $factory->withData($data);
    }
}
