<?php

declare(strict_types=1);

namespace Tempest {
    use Tempest\Mapper\ObjectFactory;

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
}
